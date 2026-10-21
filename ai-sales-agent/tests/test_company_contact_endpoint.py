import os
import unittest

from unittest.mock import (
    AsyncMock,
    patch,
)

from fastapi.testclient import (
    TestClient,
)

from app.main import app
from app.schemas import (
    CompanyContactResult,
    CompanyContactSearchResponse,
)


class CompanyContactEndpointTest(
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
                "/find-company-contacts",
                json={
                    "name":
                        "DP World",

                    "domain":
                        "dpworld.com",

                    "country":
                        "Dominican Republic",

                    "industry":
                        "Logistics",
                },
            )


        self.assertEqual(
            response.status_code,
            401,
        )

        self.assertEqual(
            response.json()["detail"],
            "Missing authorization token.",
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
                "/find-company-contacts",
                headers={
                    "Authorization":
                        "Bearer wrong-token"
                },
                json={
                    "name":
                        "DP World",

                    "domain":
                        "dpworld.com",

                    "country":
                        "Dominican Republic",

                    "industry":
                        "Logistics",
                },
            )


        self.assertEqual(
            response.status_code,
            401,
        )

        self.assertEqual(
            response.json()["detail"],
            "Invalid authorization token.",
        )


    def testReturnsCompanyContacts(
        self
    ) -> None:

        service = AsyncMock()

        service.find_contacts.return_value = (
            CompanyContactSearchResponse(
                contacts=[
                    CompanyContactResult(
                        first_name="Anna",
                        last_name="Svensson",
                        job_title=(
                            "Operations Manager"
                        ),
                        email=(
                            "anna@example.com"
                        ),
                        phone=None,
                        linkedin_url=None,
                        language="en",
                        source_url=(
                            "https://example.com/"
                            "anna-svensson"
                        ),
                        source_type=(
                            "COMPANY_WEBSITE"
                        ),
                        relevance_score=92,
                        is_primary=True,
                    ),
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
                "CompanyContactService"
            )
        ) as service_class:

            service_class.return_value = (
                service
            )


            response = self.client.post(
                "/find-company-contacts",
                headers={
                    "Authorization":
                        "Bearer test-token"
                },
                json={
                    "name":
                        "DP World",

                    "website":
                        "https://www.dpworld.com",

                    "domain":
                        "dpworld.com",

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
                        "DP World provides logistics "
                        "and warehousing services.",
                },
            )


        self.assertEqual(
            response.status_code,
            200,
        )


        data = (
            response.json()
        )


        self.assertEqual(
            len(
                data["contacts"]
            ),
            1,
        )

        self.assertEqual(
            data["contacts"][0]["first_name"],
            "Anna",
        )

        self.assertEqual(
            data["contacts"][0]["job_title"],
            "Operations Manager",
        )

        self.assertEqual(
            data["contacts"][0]["relevance_score"],
            92,
        )

        self.assertTrue(
            data["contacts"][0]["is_primary"]
        )

        self.assertEqual(
            data["contacts"][0]["source_type"],
            "COMPANY_WEBSITE",
        )

        self.assertEqual(
            data["model"],
            "test-model",
        )


        service.find_contacts.assert_awaited_once()


    def testReturnsServerErrorWhenContactSearchFails(
        self
    ) -> None:

        service = AsyncMock()

        service.find_contacts.side_effect = (
            RuntimeError(
                "Company contact search failed."
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
                "CompanyContactService"
            )
        ) as service_class:

            service_class.return_value = (
                service
            )


            response = self.client.post(
                "/find-company-contacts",
                headers={
                    "Authorization":
                        "Bearer test-token"
                },
                json={
                    "name":
                        "DP World",

                    "domain":
                        "dpworld.com",

                    "country":
                        "Dominican Republic",

                    "industry":
                        "Logistics",
                },
            )


        self.assertEqual(
            response.status_code,
            500,
        )

        self.assertEqual(
            response.json()["detail"],
            "Company contact search failed.",
        )


if __name__ == "__main__":
    unittest.main()