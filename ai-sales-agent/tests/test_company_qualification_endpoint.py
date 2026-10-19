import os
import unittest

from unittest.mock import (
    AsyncMock,
    patch,
)

from fastapi.testclient import TestClient

from app.main import app
from app.schemas import (
    CompanyQualificationResponse,
)


class CompanyQualificationEndpointTest(
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
                "/qualify-company",
                json={
                    "name":
                        "DP World",

                    "country":
                        "Dominican Republic",

                    "industry":
                        "Logistics",

                    "research_summary":
                        "DP World provides logistics "
                        "and warehousing services.",

                    "inventory_context":
                        "Warehousing and storage "
                        "operations are supported."
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
                "/qualify-company",
                headers={
                    "Authorization":
                        "Bearer wrong-token"
                },
                json={
                    "name":
                        "DP World",

                    "country":
                        "Dominican Republic",

                    "industry":
                        "Logistics",

                    "research_summary":
                        "DP World provides logistics "
                        "and warehousing services."
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


    def testReturnsCompanyQualification(
        self
    ) -> None:

        service = AsyncMock()

        service.qualify_company.return_value = (
            CompanyQualificationResponse(
                score=88,
                level="HIGH",
                reason=(
                    "Strong warehousing, storage "
                    "and distribution relevance."
                ),
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
                "CompanyQualificationService"
            )
        ) as service_class:

            service_class.return_value = (
                service
            )


            response = self.client.post(
                "/qualify-company",
                headers={
                    "Authorization":
                        "Bearer test-token"
                },
                json={
                    "name":
                        "DP World",

                    "market":
                        "LATAM",

                    "country":
                        "Dominican Republic",

                    "city":
                        "Caucedo",

                    "industry":
                        "Logistics",

                    "language":
                        "en",

                    "research_summary":
                        "DP World provides integrated "
                        "logistics and warehousing "
                        "services.",

                    "inventory_context":
                        "The company manages storage "
                        "and distribution of goods."
                },
            )


        self.assertEqual(
            response.status_code,
            200
        )


        data = response.json()


        self.assertEqual(
            data["score"],
            88
        )

        self.assertEqual(
            data["level"],
            "HIGH"
        )

        self.assertEqual(
            data["reason"],
            (
                "Strong warehousing, storage "
                "and distribution relevance."
            )
        )

        self.assertEqual(
            data["model"],
            "test-model"
        )


        service.qualify_company.assert_awaited_once()


    def testReturnsServerErrorWhenQualificationFails(
        self
    ) -> None:

        service = AsyncMock()

        service.qualify_company.side_effect = (
            RuntimeError(
                "Company qualification failed."
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
                "CompanyQualificationService"
            )
        ) as service_class:

            service_class.return_value = (
                service
            )


            response = self.client.post(
                "/qualify-company",
                headers={
                    "Authorization":
                        "Bearer test-token"
                },
                json={
                    "name":
                        "DP World",

                    "country":
                        "Dominican Republic",

                    "industry":
                        "Logistics",

                    "research_summary":
                        "DP World provides logistics "
                        "services."
                },
            )


        self.assertEqual(
            response.status_code,
            500
        )

        self.assertEqual(
            response.json()["detail"],
            "Company qualification failed."
        )


if __name__ == "__main__":
    unittest.main()