import json
import re

from textwrap import dedent

from .providers.factory import (
    create_ai_provider,
)
from .schemas import (
    CompanyResearchAnalysis,
    CompanyResearchRequest,
    CompanyResearchResponse,
)
from .search.factory import (
    create_company_search_provider,
)


class CompanyResearchService:

    async def research_company(
        self,
        request: CompanyResearchRequest,
    ) -> CompanyResearchResponse:

        search_provider = (
            create_company_search_provider()
        )

        ai_provider = (
            create_ai_provider()
        )


        query_parts = [
            request.name
        ]


        if request.domain:
            query_parts.append(
                request.domain
            )

        elif request.website:
            query_parts.append(
                request.website
            )


        if request.country:
            query_parts.append(
                request.country
            )


        if request.industry:
            query_parts.append(
                request.industry
            )


        query_parts.extend(
            [
                "company",
                "operations",
                "services",
            ]
        )


        query = " ".join(
            query_parts
        )


        results = (
            await search_provider.search_companies(
                query=query,
                limit=8,
            )
        )


        if not results:
            raise RuntimeError(
                "No company research sources were found."
            )


        result_data = []


        for index, result in enumerate(
            results
        ):
            snippet = (
                result.snippet
                or ""
            )

            snippet = re.sub(
                r"<[^>]+>",
                "",
                snippet
            )


            result_data.append(
                {
                    "result_index":
                        index,

                    "title":
                        result.title,

                    "url":
                        result.url,

                    "snippet":
                        snippet,
                }
            )


        company_context = {
            "name":
                request.name,

            "website":
                request.website,

            "domain":
                request.domain,

            "market":
                request.market,

            "country":
                request.country,

            "city":
                request.city,

            "industry":
                request.industry,

            "language":
                request.language,
        }


        instructions = dedent("""
You research companies for the AllStockControl
AI Sales system.

Your job is to create a factual company research
summary using only the supplied company context
and web search results.

STRICT RULES:

- Use only the supplied evidence.
- Never invent facts.
- Never invent locations.
- Never invent company size, employees, revenue,
  customers, technologies, warehouses, products,
  services, or business operations.
- Never invent URLs.
- Do not assume a fact merely because it would be
  typical for the company's industry.
- Existing company context may be used, but web
  evidence should be preferred when enriching it.
- If a fact cannot be supported, leave the
  corresponding field null.

SUMMARY:

- summary must explain only the most important
  factual information about the company.
- Write exactly 2 or 3 concise sentences.
- Keep the summary under 90 words.
- Focus on what the company does, its relevant
  operations, and the most important verified
  business context.
- Do not include sales recommendations.
- Do not include outreach strategy.
- Do not include suggested next steps.
- Do not explain the research process.
- Do not mention missing information.
- Do not include disclaimers, conclusions,
  sign-offs, or messages to the salesperson.
- Do not include unsupported promotional claims.

INVENTORY CONTEXT:

- inventory_context should explain any supported
  operational connection to inventory, warehousing,
  stock, distribution, logistics, products,
  fulfilment, supply chain, branches, or physical
  goods.
- Keep inventory_context to 1 or 2 concise
  sentences.

LOCATION:

- city must only be returned when supported by the
  evidence.
- Do not infer a city only from the country.

INDUSTRY:

- industry may refine the existing industry when
  supported by evidence.
- Do not guess.

LANGUAGE:

- language should represent a clearly supported
  customer-facing or company language.
- Use a short language code such as:
  en, es, sv.
- Do not infer language only from country.
- If it cannot be established, return null.

SOURCES:

- source_indexes must contain only indexes from the
  supplied web search results.
- Include only results actually used as evidence.
- Never create an index that does not exist.
- At least one supplied source must support the
  research.
""").strip()


        user_input = dedent(f"""
COMPANY:

{json.dumps(
    company_context,
    ensure_ascii=False,
    indent=2,
)}

WEB SEARCH RESULTS:

{json.dumps(
    result_data,
    ensure_ascii=False,
    indent=2,
)}

Research this company using only the supplied
evidence.
""").strip()


        research = (
            await ai_provider.generate_structured(
                instructions=instructions,
                user_input=user_input,
                response_model=
                    CompanyResearchAnalysis,
            )
        )


        sources = []


        for source_index in (
            research.data.source_indexes
        ):
            if (
                source_index < 0
                or source_index >= len(results)
            ):
                continue


            source_url = (
                results[
                    source_index
                ].url
            )


            if source_url in sources:
                continue


            sources.append(
                source_url
            )


        if not sources:
            raise RuntimeError(
                "Company research returned "
                "no valid evidence sources."
            )


        return CompanyResearchResponse(
            summary=
                research.data.summary,

            inventory_context=
                research.data.inventory_context,

            city=
                research.data.city,

            industry=
                research.data.industry,

            language=
                research.data.language,

            sources=
                sources,

            model=
                research.model,
        )