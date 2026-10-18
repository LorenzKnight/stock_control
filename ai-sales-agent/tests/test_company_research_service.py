import unittest

from unittest.mock import (
    AsyncMock,
    patch,
)

from app.company_research_service import (
    CompanyResearchService,
)
from app.providers.base import (
    AIStructuredResult,
)
from app.schemas import (
    CompanyResearchAnalysis,
    CompanyResearchRequest,
)
from app.search.base import SearchResult


class CompanyResearchServiceTest(
    unittest.IsolatedAsyncioTestCase
):

    async def testResearchesCompanyUsingSearchEvidence(
        self
    ) -> None:

        search_provider = AsyncMock()

        search_provider.search_companies.return_value = [
            SearchResult(
                title="EPS Dominican Republic",
                url="https://www.eps.com.do/en/",
                snippet=(
                    "Pioneers in courier shipping "
                    "and <strong>logistics</strong>."
                ),
            ),
            SearchResult(
                title="EPS Cargo Services",
                url="https://www.eps.com.do/cargo/",
                snippet=(
                    "Cargo and local distribution "
                    "services for businesses."
                ),
            ),
        ]


        ai_provider = AsyncMock()

        ai_provider.generate_structured.return_value = (
            AIStructuredResult(
                data=CompanyResearchAnalysis(
                    summary=(
                        "EPS provides courier, cargo "
                        "and logistics services."
                    ),
                    inventory_context=(
                        "The available evidence supports "
                        "cargo and local distribution "
                        "operations involving physical goods."
                    ),
                    city=None,
                    industry="Logistics",
                    language="es",
                    source_indexes=[
                        0,
                        1,
                    ],
                ),
                model="test-model",
            )
        )


        request = CompanyResearchRequest(
            name="EPS",
            website="https://www.eps.com.do",
            domain="eps.com.do",
            market="LATAM",
            country="Dominican Republic",
            city=None,
            industry="Logistics",
            language=None,
        )


        with patch(
            (
                "app.company_research_service."
                "create_company_search_provider"
            ),
            return_value=search_provider,
        ), patch(
            (
                "app.company_research_service."
                "create_ai_provider"
            ),
            return_value=ai_provider,
        ):
            service = CompanyResearchService()

            response = (
                await service.research_company(
                    request
                )
            )


        search_provider.search_companies.assert_awaited_once_with(
            query=(
                "EPS eps.com.do "
                "Dominican Republic Logistics "
                "company operations services"
            ),
            limit=8,
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
            user_input
        )

        self.assertIn(
            "logistics",
            user_input
        )


        self.assertEqual(
            response.summary,
            (
                "EPS provides courier, cargo "
                "and logistics services."
            )
        )

        self.assertEqual(
            response.inventory_context,
            (
                "The available evidence supports "
                "cargo and local distribution "
                "operations involving physical goods."
            )
        )

        self.assertEqual(
            response.industry,
            "Logistics"
        )

        self.assertEqual(
            response.language,
            "es"
        )

        self.assertEqual(
            response.sources,
            [
                "https://www.eps.com.do/en/",
                "https://www.eps.com.do/cargo/",
            ]
        )

        self.assertEqual(
            response.model,
            "test-model"
        )


    async def testRaisesWhenNoResearchSourcesAreFound(
        self
    ) -> None:

        search_provider = AsyncMock()

        search_provider.search_companies.return_value = []


        ai_provider = AsyncMock()


        request = CompanyResearchRequest(
            name="EPS",
            domain="eps.com.do",
            country="Dominican Republic",
            industry="Logistics",
        )


        with patch(
            (
                "app.company_research_service."
                "create_company_search_provider"
            ),
            return_value=search_provider,
        ), patch(
            (
                "app.company_research_service."
                "create_ai_provider"
            ),
            return_value=ai_provider,
        ):
            service = CompanyResearchService()

            with self.assertRaisesRegex(
                RuntimeError,
                (
                    "No company research sources "
                    "were found."
                ),
            ):
                await service.research_company(
                    request
                )


        ai_provider.generate_structured.assert_not_awaited()


    async def testIgnoresDuplicateAndInvalidSourceIndexes(
        self
    ) -> None:

        search_provider = AsyncMock()

        search_provider.search_companies.return_value = [
            SearchResult(
                title="EPS",
                url="https://www.eps.com.do/",
                snippet="Courier and logistics.",
            ),
            SearchResult(
                title="EPS Services",
                url="https://www.eps.com.do/services/",
                snippet="Distribution services.",
            ),
        ]


        ai_provider = AsyncMock()

        ai_provider.generate_structured.return_value = (
            AIStructuredResult(
                data=CompanyResearchAnalysis(
                    summary="EPS provides logistics services.",
                    inventory_context=None,
                    city=None,
                    industry="Logistics",
                    language=None,
                    source_indexes=[
                        0,
                        0,
                        99,
                        -1,
                        1,
                    ],
                ),
                model="test-model",
            )
        )


        request = CompanyResearchRequest(
            name="EPS",
            domain="eps.com.do",
            country="Dominican Republic",
            industry="Logistics",
        )


        with patch(
            (
                "app.company_research_service."
                "create_company_search_provider"
            ),
            return_value=search_provider,
        ), patch(
            (
                "app.company_research_service."
                "create_ai_provider"
            ),
            return_value=ai_provider,
        ):
            service = CompanyResearchService()

            response = (
                await service.research_company(
                    request
                )
            )


        self.assertEqual(
            response.sources,
            [
                "https://www.eps.com.do/",
                "https://www.eps.com.do/services/",
            ]
        )


    async def testRaisesWhenAiReturnsNoValidEvidenceSources(
        self
    ) -> None:

        search_provider = AsyncMock()

        search_provider.search_companies.return_value = [
            SearchResult(
                title="EPS",
                url="https://www.eps.com.do/",
                snippet="Courier and logistics.",
            )
        ]


        ai_provider = AsyncMock()

        ai_provider.generate_structured.return_value = (
            AIStructuredResult(
                data=CompanyResearchAnalysis(
                    summary="EPS provides logistics services.",
                    inventory_context=None,
                    city=None,
                    industry="Logistics",
                    language=None,
                    source_indexes=[
                        99
                    ],
                ),
                model="test-model",
            )
        )


        request = CompanyResearchRequest(
            name="EPS",
            domain="eps.com.do",
            country="Dominican Republic",
            industry="Logistics",
        )


        with patch(
            (
                "app.company_research_service."
                "create_company_search_provider"
            ),
            return_value=search_provider,
        ), patch(
            (
                "app.company_research_service."
                "create_ai_provider"
            ),
            return_value=ai_provider,
        ):
            service = CompanyResearchService()

            with self.assertRaisesRegex(
                RuntimeError,
                (
                    "Company research returned "
                    "no valid evidence sources."
                ),
            ):
                await service.research_company(
                    request
                )


if __name__ == "__main__":
    unittest.main()