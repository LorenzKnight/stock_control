import unittest

from unittest.mock import (
    AsyncMock,
    patch,
)

from app.company_discovery_service import (
    CompanyDiscoveryService,
)
from app.schemas import (
    CompanyDiscoveryRequest,
    CompanySearchCriteria,
)
from app.search.base import SearchResult

from app.schemas import (
    CompanyCandidate,
    CompanyDiscoveryRequest,
    CompanySearchCriteria,
    CompanySuggestion,
)


class CompanyDiscoveryServiceTest(
    unittest.IsolatedAsyncioTestCase
):

    async def testExecutesMultipleSearches(
        self
    ) -> None:

        provider = AsyncMock()

        provider.search_companies.return_value = [
            SearchResult(
                title="Example Company",
                url="https://example.com",
                snippet="Example result",
            )
        ]


        analyzer = AsyncMock()

        analyzer.analyze_results.side_effect = [
            [
                CompanyCandidate(
                    name="Dominican Logistics",
                    website="https://example.com",
                    domain="example.com",
                    country="Dominican Republic",
                    city=None,
                    industry="Logistics",
                    source_url="https://example.com",
                    source_text="Example result",
                    source_type="DISCOVERY",
                    search_id="dr-logistics",
                    suggested_by_user=False,
                )
            ],
            [
                CompanyCandidate(
                    name="Sweden Retail",
                    website="https://example.se",
                    domain="example.se",
                    country="Sweden",
                    city="Gothenburg",
                    industry="Retail",
                    source_url="https://example.se",
                    source_text="Example result",
                    source_type="DISCOVERY",
                    search_id="sweden-retail",
                    suggested_by_user=False,
                )
            ],
        ]


        request = CompanyDiscoveryRequest(
            searches=[
                CompanySearchCriteria(
                    search_id="dr-logistics",
                    country="Dominican Republic",
                    industry="Logistics",
                    limit=10,
                ),
                CompanySearchCriteria(
                    search_id="sweden-retail",
                    country="Sweden",
                    city="Gothenburg",
                    industry="Retail",
                    limit=5,
                ),
            ]
        )


        with patch(
            (
                "app.company_discovery_service."
                "create_company_search_provider"
            ),
            return_value=provider,
        ), patch(
            (
                "app.company_discovery_service."
                "CompanyAnalyzerService"
            ),
            return_value=analyzer,
        ):
            service = (
                CompanyDiscoveryService()
            )

            response = await service.find_companies(
                request
            )


        self.assertEqual(
            provider.search_companies.await_count,
            2
        )

        self.assertEqual(
            analyzer.analyze_results.await_count,
            2
        )


        first_call = (
            provider.search_companies
            .await_args_list[0]
        )

        self.assertEqual(
            first_call.kwargs["query"],
            (
                "Logistics "
                "Dominican Republic "
                "company"
            )
        )

        self.assertEqual(
            first_call.kwargs["limit"],
            10
        )


        second_call = (
            provider.search_companies
            .await_args_list[1]
        )

        self.assertEqual(
            second_call.kwargs["query"],
            (
                "Retail Gothenburg "
                "Sweden company"
            )
        )

        self.assertEqual(
            second_call.kwargs["limit"],
            5
        )


        self.assertEqual(
            len(response.companies),
            2
        )

        self.assertEqual(
            response.companies[0].name,
            "Dominican Logistics"
        )

        self.assertEqual(
            response.companies[0].search_id,
            "dr-logistics"
        )

        self.assertEqual(
            response.companies[1].name,
            "Sweden Retail"
        )

        self.assertEqual(
            response.companies[1].search_id,
            "sweden-retail"
        )


    async def testRemovesDuplicateCompaniesByDomain(
        self
    ) -> None:

        provider = AsyncMock()

        provider.search_companies.return_value = [
            SearchResult(
                title="DSV",
                url="https://www.dsv.com",
                snippet="Logistics company.",
            )
        ]


        analyzer = AsyncMock()

        analyzer.analyze_results.side_effect = [
            [
                CompanyCandidate(
                    name="DSV",
                    website="https://www.dsv.com",
                    domain="dsv.com",
                    country="Dominican Republic",
                    city=None,
                    industry="Logistics",
                    source_url="https://www.dsv.com",
                    source_text="Logistics company.",
                    source_type="DISCOVERY",
                    search_id="search-one",
                    suggested_by_user=False,
                )
            ],
            [
                CompanyCandidate(
                    name="DSV",
                    website="https://www.dsv.com",
                    domain="dsv.com",
                    country="Dominican Republic",
                    city=None,
                    industry="Logistics",
                    source_url="https://www.dsv.com",
                    source_text="Logistics company.",
                    source_type="DISCOVERY",
                    search_id="search-two",
                    suggested_by_user=False,
                )
            ],
        ]


        request = CompanyDiscoveryRequest(
            searches=[
                CompanySearchCriteria(
                    search_id="search-one",
                    country="Dominican Republic",
                    industry="Logistics",
                    limit=5,
                ),
                CompanySearchCriteria(
                    search_id="search-two",
                    country="Dominican Republic",
                    city="Santo Domingo",
                    industry="Logistics",
                    limit=5,
                ),
            ]
        )


        with patch(
            (
                "app.company_discovery_service."
                "create_company_search_provider"
            ),
            return_value=provider,
        ), patch(
            (
                "app.company_discovery_service."
                "CompanyAnalyzerService"
            ),
            return_value=analyzer,
        ):
            service = (
                CompanyDiscoveryService()
            )

            response = await service.find_companies(
                request
            )


        self.assertEqual(
            len(response.companies),
            1
        )

        self.assertEqual(
            response.companies[0].name,
            "DSV"
        )

        self.assertEqual(
            response.companies[0].domain,
            "dsv.com"
        )


    async def testProcessesSuggestionSeparatelyFromSearches(
        self
    ) -> None:

        provider = AsyncMock()

        provider.search_companies.return_value = [
            SearchResult(
                title="Example",
                url="https://example.com",
                snippet="Example result.",
            )
        ]


        analyzer = AsyncMock()

        analyzer.analyze_results.side_effect = [
            [
                CompanyCandidate(
                    name="Dominican Logistics",
                    website="https://example.com",
                    domain="example.com",
                    country="Dominican Republic",
                    city=None,
                    industry="Logistics",
                    source_url="https://example.com",
                    source_text="Example result.",
                    source_type="DISCOVERY",
                    search_id="dr-logistics",
                    suggested_by_user=False,
                )
            ],
            [
                CompanyCandidate(
                    name="Schad Logistics",
                    website="https://www.schad.do",
                    domain="schad.do",
                    country="Dominican Republic",
                    city="Santo Domingo",
                    industry="Logistics",
                    source_url="https://www.schad.do",
                    source_text="Logistics company.",
                    source_type="DISCOVERY",
                    search_id="suggestion-1",
                    suggested_by_user=False,
                )
            ],
        ]


        request = CompanyDiscoveryRequest(
            searches=[
                CompanySearchCriteria(
                    search_id="dr-logistics",
                    country="Dominican Republic",
                    industry="Logistics",
                    limit=10,
                )
            ],
            suggestions=[
                CompanySuggestion(
                    name="Schad Logistics",
                    website="https://www.schad.do",
                    country="Dominican Republic",
                    note=(
                        "Potential prospect."
                    ),
                )
            ],
        )


        with patch(
            (
                "app.company_discovery_service."
                "create_company_search_provider"
            ),
            return_value=provider,
        ), patch(
            (
                "app.company_discovery_service."
                "CompanyAnalyzerService"
            ),
            return_value=analyzer,
        ):
            service = (
                CompanyDiscoveryService()
            )

            response = await service.find_companies(
                request
            )


        self.assertEqual(
            provider.search_companies.await_count,
            2
        )


        first_call = (
            provider.search_companies
            .await_args_list[0]
        )

        self.assertEqual(
            first_call.kwargs["limit"],
            10
        )


        second_call = (
            provider.search_companies
            .await_args_list[1]
        )

        self.assertEqual(
            second_call.kwargs["limit"],
            5
        )


        self.assertEqual(
            len(response.companies),
            2
        )


        discovery_company = (
            response.companies[0]
        )

        suggestion_company = (
            response.companies[1]
        )


        self.assertFalse(
            discovery_company
            .suggested_by_user
        )

        self.assertEqual(
            discovery_company.source_type,
            "DISCOVERY"
        )


        self.assertTrue(
            suggestion_company
            .suggested_by_user
        )

        self.assertEqual(
            suggestion_company.source_type,
            "SUGGESTION"
        )

        self.assertIsNone(
            suggestion_company.search_id
        )


    async def testMarksDiscoveredCompanyAsSuggestedWhenDomainMatches(
        self
    ) -> None:

        service = CompanyDiscoveryService()


        discovery_company = CompanyCandidate(
            name="Schad Logistics",
            website="https://www.schad.do",
            domain="schad.do",
            country="Dominican Republic",
            city="Santo Domingo",
            industry="Logistics",
            source_url="https://www.schad.do/en/",
            source_text="Logistics company.",
            source_type="DISCOVERY",
            search_id="dr-logistics",
            suggested_by_user=False,
        )


        suggested_company = CompanyCandidate(
            name="Schad Logistics",
            website="https://www.schad.do",
            domain="schad.do",
            country="Dominican Republic",
            city="Santo Domingo",
            industry="Logistics",
            source_url="https://www.schad.do",
            source_text="Logistics company.",
            source_type="SUGGESTION",
            search_id=None,
            suggested_by_user=True,
        )


        companies = (
            service.deduplicate_companies(
                [
                    discovery_company,
                    suggested_company,
                ]
            )
        )


        self.assertEqual(
            len(companies),
            1
        )

        self.assertEqual(
            companies[0].name,
            "Schad Logistics"
        )

        self.assertEqual(
            companies[0].domain,
            "schad.do"
        )

        self.assertTrue(
            companies[0].suggested_by_user
        )

        self.assertEqual(
            companies[0].source_type,
            "DISCOVERY"
        )

        self.assertEqual(
            companies[0].search_id,
            "dr-logistics"
        )


if __name__ == "__main__":
    unittest.main()