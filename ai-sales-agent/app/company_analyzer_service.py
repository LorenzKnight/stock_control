import json

from textwrap import dedent
from urllib.parse import urlparse

from .providers.factory import (
    create_ai_provider,
)
from .schemas import (
    CompanyAnalysisResponse,
    CompanyCandidate,
    CompanySearchCriteria,
)
from .search.base import SearchResult


class CompanyAnalyzerService:

    async def analyze_results(
        self,
        search: CompanySearchCriteria,
        results: list[SearchResult],
    ) -> list[CompanyCandidate]:

        if not results:
            return []


        provider = create_ai_provider()


        result_data = []

        for index, result in enumerate(
            results
        ):
            result_data.append(
                {
                    "result_index":
                        index,

                    "title":
                        result.title,

                    "url":
                        result.url,

                    "snippet":
                        result.snippet,
                }
            )


        search_context = {
            "search_id":
                search.search_id,

            "market":
                search.market,

            "country":
                search.country,

            "city":
                search.city,

            "industry":
                search.industry,
        }


        instructions = dedent("""
You analyze web search results to identify real companies
that could be sales prospects for AllStockControl.

STRICT RULES:

- Only analyze the supplied search results.
- Never invent companies.
- Never invent URLs.
- Never invent facts that are not supported by the supplied
  title, URL, snippet, or search criteria.
- Direct company websites may be accepted.
- Company-specific pages on official company websites may
  be accepted.
- Reject directories, aggregators, list articles, search
  result pages, social networks, news articles, government
  pages, job boards, and unrelated websites.
- is_company must only be true when the result appears to
  represent a real identifiable company.
- matches_search must only be true when the available
  evidence supports the requested country/location and
  industry criteria.
- If evidence is insufficient, reject the result.
- Every result must be represented exactly once using its
  original result_index.

COMPANY NAME RULES:

- company_name is required for every result.
- company_name must come only from the supplied evidence.
- When is_company is true, company_name must contain only
  the identifiable company or brand name.
- Do not use the complete web page title as company_name.
- Remove country labels, page descriptions, slogans,
  separators, service descriptions, and page-specific wording.
- For rejected results such as directories or aggregators,
  company_name should contain the identifiable website,
  organization, or brand name supported by the result.
- Never invent a company_name.
- Examples:
  "Dominican Republic | DSV" -> "DSV"
  "Logistics Services In The Dominican Republic | DP World"
  -> "DP World"
  "Freight and Logistics Companies in Dominican Republic | Freightnet"
  -> "Freightnet"

INDUSTRY AND LOCATION RULES:

- Do not guess city or industry when they are not supported.
- When matches_search is true and an industry criterion was
  supplied, industry should contain the supported company
  industry when the evidence confirms that criterion.
- If a city is not explicitly supported by the supplied
  evidence, city must be null.
- If an industry cannot be determined from the supplied
  evidence, industry must be null.

REJECTION RULES:

- When a result is rejected, set is_company or matches_search
  to false as appropriate.
- rejection_reason should briefly explain why the result was
  rejected.
- Do not reject a direct company website merely because the
  page title contains a country, service, or marketing phrase.
""").strip()


        user_input = dedent(f"""
SEARCH CRITERIA:

{json.dumps(
    search_context,
    ensure_ascii=False,
    indent=2,
)}

WEB SEARCH RESULTS:

{json.dumps(
    result_data,
    ensure_ascii=False,
    indent=2,
)}

Classify every supplied result.
""").strip()


        analysis = (
            await provider.generate_structured(
                instructions=instructions,
                user_input=user_input,
                response_model=
                    CompanyAnalysisResponse,
            )
        )


        candidates = []


        result_map = {
            index: result
            for index, result
            in enumerate(results)
        }


        for analyzed in analysis.data.results:

            if not analyzed.is_company:
                continue

            if not analyzed.matches_search:
                continue

            source = result_map.get(
                analyzed.result_index
            )

            if source is None:
                continue


            parsed_url = urlparse(
                source.url
            )

            if not parsed_url.scheme \
                    or not parsed_url.netloc:
                continue


            website = (
                f"{parsed_url.scheme}://"
                f"{parsed_url.netloc}"
            )

            domain = (
                parsed_url.netloc
                .lower()
            )

            if domain.startswith(
                "www."
            ):
                domain = domain[4:]


            company_name = (
                analyzed.company_name
                or ""
            ).strip()

            if company_name == "":
                continue


            candidates.append(
                CompanyCandidate(
                    name=company_name,
                    website=website,
                    domain=domain,
                    country=search.country,
                    city=analyzed.city,
                    industry=(
                        analyzed.industry
                        or search.industry
                    ),
                    source_url=source.url,
                    source_text=source.snippet,
                    source_type="DISCOVERY",
                    search_id=search.search_id,
                    suggested_by_user=False,
                )
            )


        return candidates