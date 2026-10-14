from .schemas import (
    CompanyDiscoveryRequest,
    CompanyDiscoveryResponse,
    CompanySearchCriteria,
)
from .search.factory import (
    create_company_search_provider,
)
from .company_analyzer_service import (
    CompanyAnalyzerService,
)


class CompanyDiscoveryService:

    def deduplicate_companies(
        self,
        companies: list,
    ) -> list:

        unique_companies = []

        domain_indexes = {}


        for company in companies:

            domain = (
                company.domain
                or ""
            ).strip().lower()


            if not domain:
                unique_companies.append(
                    company
                )

                continue


            if domain in domain_indexes:

                existing_index = (
                    domain_indexes[
                        domain
                    ]
                )

                existing_company = (
                    unique_companies[
                        existing_index
                    ]
                )


                if company.suggested_by_user:

                    unique_companies[
                        existing_index
                    ] = (
                        existing_company
                        .model_copy(
                            update={
                                "suggested_by_user":
                                    True,
                            }
                        )
                    )


                continue


            domain_indexes[
                domain
            ] = len(
                unique_companies
            )

            unique_companies.append(
                company
            )


        return unique_companies
    

    async def find_companies(
        self,
        request: CompanyDiscoveryRequest,
    ) -> CompanyDiscoveryResponse:

        search_provider = (
            create_company_search_provider()
        )

        analyzer = CompanyAnalyzerService()

        companies = []


        for search in request.searches:
            query_parts = []


            if search.industry:
                query_parts.append(
                    search.industry
                )

            if search.city:
                query_parts.append(
                    search.city
                )

            if search.country:
                query_parts.append(
                    search.country
                )


            query_parts.append(
                "company"
            )


            query = " ".join(
                query_parts
            )


            results = (
                await search_provider.search_companies(
                    query=query,
                    limit=search.limit,
                )
            )


            candidates = (
                await analyzer.analyze_results(
                    search=search,
                    results=results,
                )
            )


            companies.extend(
                candidates
            )


        for index, suggestion in enumerate(
            request.suggestions
        ):
            query_parts = [
                suggestion.name
            ]


            if suggestion.website:
                query_parts.append(
                    suggestion.website
                )

            if suggestion.country:
                query_parts.append(
                    suggestion.country
                )


            query = " ".join(
                query_parts
            )


            results = (
                await search_provider.search_companies(
                    query=query,
                    limit=5,
                )
            )


            suggestion_search = (
                CompanySearchCriteria(
                    search_id=(
                        f"suggestion-{index + 1}"
                    ),
                    country=suggestion.country,
                    industry=None,
                    limit=5,
                )
            )


            candidates = (
                await analyzer.analyze_results(
                    search=suggestion_search,
                    results=results,
                )
            )


            for candidate in candidates:

                companies.append(
                    candidate.model_copy(
                        update={
                            "source_type":
                                "SUGGESTION",

                            "search_id":
                                None,

                            "suggested_by_user":
                                True,
                        }
                    )
                )


        companies = (
            self.deduplicate_companies(
                companies
            )
        )


        return CompanyDiscoveryResponse(
            companies=companies
        )