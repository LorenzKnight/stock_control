import unittest

from unittest.mock import (
    AsyncMock,
    patch,
)

from app.company_contact_service import (
    CompanyContactService,
)
from app.providers.base import (
    AIStructuredResult,
)
from app.schemas import (
    CompanyContactAnalysis,
    CompanyContactCandidate,
    CompanyContactSearchRequest,
)
from app.search.base import (
    SearchResult,
)


class CompanyContactServiceTest(
    unittest.IsolatedAsyncioTestCase
):

    def testSourceTypeDetectsKnownSources(
        self
    ) -> None:

        service = (
            CompanyContactService()
        )


        self.assertEqual(
            service.source_type(
                "https://www.dpworld.com/team/person",
                "dpworld.com",
            ),
            "COMPANY_WEBSITE",
        )

        self.assertEqual(
            service.source_type(
                "https://www.linkedin.com/in/person",
                "dpworld.com",
            ),
            "LINKEDIN",
        )

        self.assertEqual(
            service.source_type(
                "https://example.com/person",
                "dpworld.com",
            ),
            "WEB",
        )


    async def testFindsRanksAndSelectsPrimaryContact(
        self
    ) -> None:

        search_provider = AsyncMock()

        search_provider.search_companies.side_effect = [
            [
                SearchResult(
                    title=(
                        "Anna Svensson - "
                        "Operations Manager"
                    ),
                    url=(
                        "https://www.dpworld.com/"
                        "team/anna-svensson"
                    ),
                    snippet=(
                        "Anna Svensson is "
                        "<strong>Operations Manager</strong> "
                        "at DP World."
                    ),
                ),
            ],
            [
                SearchResult(
                    title=(
                        "Pedro Martinez - "
                        "Supply Chain Director"
                    ),
                    url=(
                        "https://www.linkedin.com/"
                        "in/pedro-martinez"
                    ),
                    snippet=(
                        "Supply Chain Director "
                        "at DP World."
                    ),
                ),
                SearchResult(
                    title="Anna Svensson",
                    url=(
                        "https://www.dpworld.com/"
                        "team/anna-svensson"
                    ),
                    snippet=(
                        "Duplicate result."
                    ),
                ),
            ],
            [],
        ]


        ai_provider = AsyncMock()

        ai_provider.generate_structured.return_value = (
            AIStructuredResult(
                data=CompanyContactAnalysis(
                    contacts=[
                        CompanyContactCandidate(
                            first_name="Anna",
                            last_name="Svensson",
                            job_title=(
                                "Operations Manager"
                            ),
                            email=None,
                            phone=None,
                            linkedin_url=None,
                            language="en",
                            source_index=0,
                            relevance_score=82,
                        ),
                        CompanyContactCandidate(
                            first_name="Pedro",
                            last_name="Martinez",
                            job_title=(
                                "Supply Chain Director"
                            ),
                            email=None,
                            phone=None,
                            linkedin_url=(
                                "https://www.linkedin.com/"
                                "in/pedro-martinez"
                            ),
                            language="en",
                            source_index=1,
                            relevance_score=95,
                        ),
                    ]
                ),
                model="test-model",
            )
        )


        request = CompanyContactSearchRequest(
            name="DP World",
            website="https://www.dpworld.com",
            domain="dpworld.com",
            market="LATAM",
            country="Dominican Republic",
            city="Caucedo",
            industry="Logistics",
            language="en",
            research_summary=(
                "DP World provides logistics, "
                "warehousing and supply chain "
                "services."
            ),
        )


        with patch(
            (
                "app.company_contact_service."
                "create_company_search_provider"
            ),
            return_value=search_provider,
        ), patch(
            (
                "app.company_contact_service."
                "create_ai_provider"
            ),
            return_value=ai_provider,
        ):
            service = (
                CompanyContactService()
            )

            response = (
                await service.find_contacts(
                    request
                )
            )


        self.assertEqual(
            search_provider
            .search_companies
            .await_count,
            3,
        )


        ai_provider.generate_structured.assert_awaited_once()


        ai_call = (
            ai_provider.generate_structured
            .await_args
        )

        user_input = (
            ai_call.kwargs[
                "user_input"
            ]
        )


        self.assertNotIn(
            "<strong>",
            user_input,
        )

        self.assertIn(
            "Operations Manager",
            user_input,
        )

        self.assertIn(
            "Supply Chain Director",
            user_input,
        )


        self.assertEqual(
            len(
                response.contacts
            ),
            2,
        )


        primary_contact = (
            response.contacts[0]
        )

        secondary_contact = (
            response.contacts[1]
        )


        self.assertEqual(
            primary_contact.first_name,
            "Pedro",
        )

        self.assertEqual(
            primary_contact.relevance_score,
            95,
        )

        self.assertTrue(
            primary_contact.is_primary
        )

        self.assertEqual(
            primary_contact.source_type,
            "LINKEDIN",
        )


        self.assertEqual(
            secondary_contact.first_name,
            "Anna",
        )

        self.assertEqual(
            secondary_contact.relevance_score,
            82,
        )

        self.assertFalse(
            secondary_contact.is_primary
        )

        self.assertEqual(
            secondary_contact.source_type,
            "COMPANY_WEBSITE",
        )

        self.assertIsNone(
            secondary_contact.email
        )

        self.assertIsNone(
            secondary_contact.phone
        )


        self.assertEqual(
            response.model,
            "test-model",
        )


    async def testReturnsEmptyWithoutCallingAiWhenNoResultsFound(
        self
    ) -> None:

        search_provider = AsyncMock()

        search_provider.search_companies.side_effect = [
            [],
            [],
            [],
        ]


        ai_provider = AsyncMock()


        request = CompanyContactSearchRequest(
            name="Example Company",
            domain="example.com",
            country="Sweden",
            industry="Logistics",
        )


        with patch(
            (
                "app.company_contact_service."
                "create_company_search_provider"
            ),
            return_value=search_provider,
        ), patch(
            (
                "app.company_contact_service."
                "create_ai_provider"
            ),
            return_value=ai_provider,
        ):
            service = (
                CompanyContactService()
            )

            response = (
                await service.find_contacts(
                    request
                )
            )


        self.assertEqual(
            response.contacts,
            [],
        )

        self.assertEqual(
            response.model,
            "",
        )

        ai_provider.generate_structured.assert_not_awaited()


    async def testIgnoresContactWithInvalidSourceIndex(
        self
    ) -> None:

        search_provider = AsyncMock()

        search_provider.search_companies.side_effect = [
            [
                SearchResult(
                    title="Anna Svensson",
                    url=(
                        "https://example.com/"
                        "anna-svensson"
                    ),
                    snippet=(
                        "Anna Svensson is "
                        "Operations Manager."
                    ),
                ),
            ],
            [],
            [],
        ]


        ai_provider = AsyncMock()

        ai_provider.generate_structured.return_value = (
            AIStructuredResult(
                data=CompanyContactAnalysis(
                    contacts=[
                        CompanyContactCandidate(
                            first_name="Invalid",
                            last_name="Person",
                            job_title="Director",
                            source_index=99,
                            relevance_score=99,
                        ),
                        CompanyContactCandidate(
                            first_name="Anna",
                            last_name="Svensson",
                            job_title=(
                                "Operations Manager"
                            ),
                            source_index=0,
                            relevance_score=80,
                        ),
                    ]
                ),
                model="test-model",
            )
        )


        request = CompanyContactSearchRequest(
            name="Example Company",
            domain="example.com",
            industry="Logistics",
        )


        with patch(
            (
                "app.company_contact_service."
                "create_company_search_provider"
            ),
            return_value=search_provider,
        ), patch(
            (
                "app.company_contact_service."
                "create_ai_provider"
            ),
            return_value=ai_provider,
        ):
            service = (
                CompanyContactService()
            )

            response = (
                await service.find_contacts(
                    request
                )
            )


        self.assertEqual(
            len(
                response.contacts
            ),
            1,
        )

        self.assertEqual(
            response.contacts[0].first_name,
            "Anna",
        )

        self.assertTrue(
            response.contacts[0].is_primary
        )


    async def testReturnsMaximumThreeContacts(
        self
    ) -> None:

        search_provider = AsyncMock()

        search_provider.search_companies.side_effect = [
            [
                SearchResult(
                    title="Contact 1",
                    url="https://example.com/contact-1",
                    snippet="Operations Director.",
                ),
                SearchResult(
                    title="Contact 2",
                    url="https://example.com/contact-2",
                    snippet="Warehouse Manager.",
                ),
                SearchResult(
                    title="Contact 3",
                    url="https://example.com/contact-3",
                    snippet="Supply Chain Manager.",
                ),
                SearchResult(
                    title="Contact 4",
                    url="https://example.com/contact-4",
                    snippet="CEO.",
                ),
            ],
            [],
            [],
        ]


        ai_provider = AsyncMock()

        ai_provider.generate_structured.return_value = (
            AIStructuredResult(
                data=CompanyContactAnalysis(
                    contacts=[
                        CompanyContactCandidate(
                            first_name="Contact",
                            last_name="One",
                            job_title=(
                                "Operations Director"
                            ),
                            source_index=0,
                            relevance_score=95,
                        ),
                        CompanyContactCandidate(
                            first_name="Contact",
                            last_name="Two",
                            job_title=(
                                "Warehouse Manager"
                            ),
                            source_index=1,
                            relevance_score=90,
                        ),
                        CompanyContactCandidate(
                            first_name="Contact",
                            last_name="Three",
                            job_title=(
                                "Supply Chain Manager"
                            ),
                            source_index=2,
                            relevance_score=85,
                        ),
                        CompanyContactCandidate(
                            first_name="Contact",
                            last_name="Four",
                            job_title="CEO",
                            source_index=3,
                            relevance_score=50,
                        ),
                    ]
                ),
                model="test-model",
            )
        )


        request = CompanyContactSearchRequest(
            name="Example Company",
            domain="example.com",
            industry="Logistics",
        )


        with patch(
            (
                "app.company_contact_service."
                "create_company_search_provider"
            ),
            return_value=search_provider,
        ), patch(
            (
                "app.company_contact_service."
                "create_ai_provider"
            ),
            return_value=ai_provider,
        ):
            service = (
                CompanyContactService()
            )

            response = (
                await service.find_contacts(
                    request
                )
            )


        self.assertEqual(
            len(
                response.contacts
            ),
            3,
        )

        self.assertEqual(
            response.contacts[0].relevance_score,
            95,
        )

        self.assertTrue(
            response.contacts[0].is_primary
        )

        self.assertFalse(
            response.contacts[1].is_primary
        )

        self.assertFalse(
            response.contacts[2].is_primary
        )


if __name__ == "__main__":
    unittest.main()