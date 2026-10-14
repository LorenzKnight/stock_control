import unittest

from unittest.mock import (
    AsyncMock,
    patch,
)

from app.company_analyzer_service import (
    CompanyAnalyzerService,
)
from app.providers.base import (
    AIStructuredResult,
)
from app.schemas import (
    CompanyAnalysisItem,
    CompanyAnalysisResponse,
    CompanySearchCriteria,
)
from app.search.base import SearchResult


class CompanyAnalyzerServiceTest(
    unittest.IsolatedAsyncioTestCase
):

    async def testReturnsEmptyListWhenThereAreNoResults(
        self
    ) -> None:

        service = CompanyAnalyzerService()

        search = CompanySearchCriteria(
            search_id="dr-logistics",
            country="Dominican Republic",
            industry="Logistics",
            limit=5,
        )


        with patch(
            (
                "app.company_analyzer_service."
                "create_ai_provider"
            )
        ) as provider_factory:

            result = await service.analyze_results(
                search=search,
                results=[],
            )


        self.assertEqual(
            result,
            []
        )

        provider_factory.assert_not_called()


    async def testRejectsDirectoryAndCreatesCompanyCandidate(
        self
    ) -> None:

        provider = AsyncMock()

        provider.generate_structured.return_value = (
            AIStructuredResult(
                data=CompanyAnalysisResponse(
                    results=[
                        CompanyAnalysisItem(
                            result_index=0,
                            is_company=False,
                            matches_search=False,
                            company_name="Freightnet",
                            industry=None,
                            city=None,
                            rejection_reason=(
                                "Directory / aggregator"
                            ),
                        ),
                        CompanyAnalysisItem(
                            result_index=1,
                            is_company=True,
                            matches_search=True,
                            company_name="Schad Logistics",
                            industry="Logistics",
                            city="Santo Domingo",
                            rejection_reason=None,
                        ),
                    ]
                ),
                model="test-model",
            )
        )


        search = CompanySearchCriteria(
            search_id="dr-logistics",
            market="LATAM",
            country="Dominican Republic",
            industry="Logistics",
            limit=5,
        )


        results = [
            SearchResult(
                title=(
                    "Freight and Logistics Companies "
                    "in Dominican Republic | Freightnet"
                ),
                url=(
                    "https://www.freightnet.com/"
                    "directory/p1/cDO/s99.htm"
                ),
                snippet=(
                    "Directory of freight and "
                    "logistics companies."
                ),
            ),
            SearchResult(
                title=(
                    "Schad Logistics – Llegas Primero"
                ),
                url="https://www.schad.do/en/",
                snippet=(
                    "Full service logistics operator "
                    "based in Santo Domingo, "
                    "Dominican Republic."
                ),
            ),
        ]


        with patch(
            (
                "app.company_analyzer_service."
                "create_ai_provider"
            ),
            return_value=provider,
        ):
            service = CompanyAnalyzerService()

            candidates = (
                await service.analyze_results(
                    search=search,
                    results=results,
                )
            )


        self.assertEqual(
            len(candidates),
            1
        )

        candidate = candidates[0]


        self.assertEqual(
            candidate.name,
            "Schad Logistics"
        )

        self.assertEqual(
            candidate.website,
            "https://www.schad.do"
        )

        self.assertEqual(
            candidate.domain,
            "schad.do"
        )

        self.assertEqual(
            candidate.country,
            "Dominican Republic"
        )

        self.assertEqual(
            candidate.city,
            "Santo Domingo"
        )

        self.assertEqual(
            candidate.industry,
            "Logistics"
        )

        self.assertEqual(
            candidate.source_url,
            "https://www.schad.do/en/"
        )

        self.assertEqual(
            candidate.source_type,
            "DISCOVERY"
        )

        self.assertEqual(
            candidate.search_id,
            "dr-logistics"
        )

        self.assertFalse(
            candidate.suggested_by_user
        )


    async def testRejectsCompanyThatDoesNotMatchSearch(
        self
    ) -> None:

        provider = AsyncMock()

        provider.generate_structured.return_value = (
            AIStructuredResult(
                data=CompanyAnalysisResponse(
                    results=[
                        CompanyAnalysisItem(
                            result_index=0,
                            is_company=True,
                            matches_search=False,
                            company_name="Example Company",
                            industry="Software",
                            city=None,
                            rejection_reason=(
                                "Company does not match "
                                "the requested industry."
                            ),
                        ),
                    ]
                ),
                model="test-model",
            )
        )


        search = CompanySearchCriteria(
            search_id="dr-logistics",
            country="Dominican Republic",
            industry="Logistics",
            limit=5,
        )


        results = [
            SearchResult(
                title="Example Company",
                url="https://example.com",
                snippet=(
                    "Software development company."
                ),
            )
        ]


        with patch(
            (
                "app.company_analyzer_service."
                "create_ai_provider"
            ),
            return_value=provider,
        ):
            service = CompanyAnalyzerService()

            candidates = (
                await service.analyze_results(
                    search=search,
                    results=results,
                )
            )


        self.assertEqual(
            candidates,
            []
        )


if __name__ == "__main__":
    unittest.main()