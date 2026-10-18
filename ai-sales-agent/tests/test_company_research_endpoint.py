import os
import unittest

from unittest.mock import (
    AsyncMock,
    patch,
)

from fastapi.testclient import TestClient

from app.main import app
from app.schemas import (
    CompanyResearchResponse,
)


class CompanyResearchEndpointTest(
    unittest.TestCase
):

    def setUp(
        self
    ) -> None:
        self.client = TestClient(
            app
        )


    def testRejectsMissingAuthorizationToken(
        self
    ) -> None:

        with patch.dict(
            os.environ,
            {
                "AI_SALES_AGENT_TOKEN":
                    "test-token"
            },
        ):
            response = self.client.post(
                "/research-company",
                json={
                    "name":
                        "EPS",

                    "domain":
                        "eps.com.do",

                    "country":
                        "Dominican Republic",

                    "industry":
                        "Logistics",
                },
            )


        self.assertEqual(
            response.status_code,
            401
        )

        self.assertEqual(
            response.json()["detail"],
            "Missing authorization token."
        )


    def testRejectsInvalidAuthorizationToken(
        self
    ) -> None:

        with patch.dict(
            os.environ,
            {
                "AI_SALES_AGENT_TOKEN":
                    "test-token"
            },
        ):
            response = self.client.post(
                "/research-company",
                headers={
                    "Authorization":
                        "Bearer wrong-token"
                },
                json={
                    "name":
                        "EPS",

                    "domain":
                        "eps.com.do",

                    "country":
                        "Dominican Republic",

                    "industry":
                        "Logistics",
                },
            )


        self.assertEqual(
            response.status_code,
            401
        )

        self.assertEqual(
            response.json()["detail"],
            "Invalid authorization token."
        )


    def testReturnsCompanyResearch(
        self
    ) -> None:

        service = AsyncMock()

        service.research_company.return_value = (
            CompanyResearchResponse(
                summary=(
                    "EPS provides courier, cargo "
                    "and logistics services."
                ),
                inventory_context=(
                    "The company provides cargo "
                    "and distribution services."
                ),
                city=None,
                industry="Logistics",
                language="es",
                sources=[
                    "https://www.eps.com.do/en/",
                ],
                model="test-model",
            )
        )


        with patch.dict(
            os.environ,
            {
                "AI_SALES_AGENT_TOKEN":
                    "test-token"
            },
        ), patch(
            (
                "app.main."
                "CompanyResearchService"
            )
        ) as service_class:

            service_class.return_value = (
                service
            )

            response = self.client.post(
                "/research-company",
                headers={
                    "Authorization":
                        "Bearer test-token"
                },
                json={
                    "name":
                        "EPS",

                    "website":
                        "https://www.eps.com.do",

                    "domain":
                        "eps.com.do",

                    "market":
                        "LATAM",

                    "country":
                        "Dominican Republic",

                    "city":
                        None,

                    "industry":
                        "Logistics",

                    "language":
                        None,
                },
            )


        self.assertEqual(
            response.status_code,
            200
        )


        data = response.json()


        self.assertEqual(
            data["summary"],
            (
                "EPS provides courier, cargo "
                "and logistics services."
            )
        )

        self.assertEqual(
            data["inventory_context"],
            (
                "The company provides cargo "
                "and distribution services."
            )
        )

        self.assertEqual(
            data["industry"],
            "Logistics"
        )

        self.assertEqual(
            data["language"],
            "es"
        )

        self.assertEqual(
            data["sources"],
            [
                "https://www.eps.com.do/en/"
            ]
        )

        self.assertEqual(
            data["model"],
            "test-model"
        )


        service.research_company.assert_awaited_once()


    def testReturnsServerErrorWhenResearchFails(
        self
    ) -> None:

        service = AsyncMock()

        service.research_company.side_effect = (
            RuntimeError(
                "Company research failed."
            )
        )


        with patch.dict(
            os.environ,
            {
                "AI_SALES_AGENT_TOKEN":
                    "test-token"
            },
        ), patch(
            (
                "app.main."
                "CompanyResearchService"
            )
        ) as service_class:

            service_class.return_value = (
                service
            )

            response = self.client.post(
                "/research-company",
                headers={
                    "Authorization":
                        "Bearer test-token"
                },
                json={
                    "name":
                        "EPS",

                    "domain":
                        "eps.com.do",

                    "country":
                        "Dominican Republic",

                    "industry":
                        "Logistics",
                },
            )


        self.assertEqual(
            response.status_code,
            500
        )

        self.assertEqual(
            response.json()["detail"],
            "Company research failed."
        )


if __name__ == "__main__":
    unittest.main()