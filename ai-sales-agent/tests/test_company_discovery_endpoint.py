import os
import unittest

from unittest.mock import (
    AsyncMock,
    patch,
)

from fastapi.testclient import TestClient

from app.main import app
from app.schemas import (
    CompanyCandidate,
    CompanyDiscoveryResponse,
)


class CompanyDiscoveryEndpointTest(
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
                "/discover-companies",
                json={
                    "searches": [
                        {
                            "search_id":
                                "dr-logistics",
                            "country":
                                "Dominican Republic",
                            "industry":
                                "Logistics",
                            "limit":
                                5,
                        }
                    ]
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
                "/discover-companies",
                headers={
                    "Authorization":
                        "Bearer wrong-token"
                },
                json={
                    "searches": [
                        {
                            "search_id":
                                "dr-logistics",
                            "country":
                                "Dominican Republic",
                            "industry":
                                "Logistics",
                            "limit":
                                5,
                        }
                    ]
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


    def testReturnsDiscoveredCompanies(
        self
    ) -> None:

        service = AsyncMock()

        service.find_companies.return_value = (
            CompanyDiscoveryResponse(
                companies=[
                    CompanyCandidate(
                        name="DSV",
                        website="https://www.dsv.com",
                        domain="dsv.com",
                        country="Dominican Republic",
                        city=None,
                        industry="Logistics",
                        source_url=(
                            "https://www.dsv.com/"
                            "en/countries/americas/"
                            "dominican-republic"
                        ),
                        source_text=(
                            "Transport and logistics "
                            "services."
                        ),
                        source_type="DISCOVERY",
                        search_id="dr-logistics",
                        suggested_by_user=True,
                    )
                ]
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
                "CompanyDiscoveryService"
            )
        ) as service_class:

            service_class.return_value = (
                service
            )

            response = self.client.post(
                "/discover-companies",
                headers={
                    "Authorization":
                        "Bearer test-token"
                },
                json={
                    "searches": [
                        {
                            "search_id":
                                "dr-logistics",
                            "market":
                                "LATAM",
                            "country":
                                "Dominican Republic",
                            "industry":
                                "Logistics",
                            "language":
                                "es",
                            "limit":
                                5,
                        }
                    ],
                    "suggestions": [
                        {
                            "name":
                                "DSV",
                            "website":
                                "https://www.dsv.com",
                            "country":
                                "Dominican Republic",
                            "note":
                                "Potential prospect.",
                        }
                    ],
                },
            )


        self.assertEqual(
            response.status_code,
            200
        )


        data = response.json()

        self.assertEqual(
            len(data["companies"]),
            1
        )

        self.assertEqual(
            data["companies"][0]["name"],
            "DSV"
        )

        self.assertEqual(
            data["companies"][0]["domain"],
            "dsv.com"
        )

        self.assertEqual(
            data["companies"][0]["source_type"],
            "DISCOVERY"
        )

        self.assertTrue(
            data["companies"][0][
                "suggested_by_user"
            ]
        )


        service.find_companies.assert_awaited_once()


    def testReturnsServerErrorWhenDiscoveryFails(
        self
    ) -> None:

        service = AsyncMock()

        service.find_companies.side_effect = (
            RuntimeError(
                "Company discovery failed."
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
                "CompanyDiscoveryService"
            )
        ) as service_class:

            service_class.return_value = (
                service
            )

            response = self.client.post(
                "/discover-companies",
                headers={
                    "Authorization":
                        "Bearer test-token"
                },
                json={
                    "searches": [
                        {
                            "search_id":
                                "dr-logistics",
                            "country":
                                "Dominican Republic",
                            "industry":
                                "Logistics",
                            "limit":
                                5,
                        }
                    ]
                },
            )


        self.assertEqual(
            response.status_code,
            500
        )

        self.assertEqual(
            response.json()["detail"],
            "Company discovery failed."
        )


if __name__ == "__main__":
    unittest.main()