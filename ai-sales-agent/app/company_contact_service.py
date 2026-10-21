import json
import re

from textwrap import dedent
from urllib.parse import urlparse

from .providers.factory import (
    create_ai_provider,
)
from .schemas import (
    CompanyContactAnalysis,
    CompanyContactResult,
    CompanyContactSearchRequest,
    CompanyContactSearchResponse,
)
from .search.factory import (
    create_company_search_provider,
)


class CompanyContactService:

    def source_type(
        self,
        url: str,
        company_domain: str | None,
    ) -> str:

        host = (
            urlparse(
                url
            ).netloc
            .lower()
            .removeprefix(
                "www."
            )
        )


        if (
            company_domain
            and host.endswith(
                company_domain
                .lower()
                .removeprefix(
                    "www."
                )
            )
        ):
            return "COMPANY_WEBSITE"


        if (
            host == "linkedin.com"
            or host.endswith(
                ".linkedin.com"
            )
        ):
            return "LINKEDIN"


        return "WEB"


    async def find_contacts(
        self,
        request: CompanyContactSearchRequest,
    ) -> CompanyContactSearchResponse:

        search_provider = (
            create_company_search_provider()
        )

        ai_provider = (
            create_ai_provider()
        )


        company_reference = (
            request.domain
            or request.website
            or request.name
        )


        queries = [
            (
                f'"{request.name}" '
                f'"operations manager" '
                f'{company_reference}'
            ),
            (
                f'"{request.name}" '
                f'"supply chain" OR '
                f'"warehouse manager" '
                f'{company_reference}'
            ),
            (
                f'"{request.name}" '
                f'LinkedIn operations logistics '
                f'{company_reference}'
            ),
        ]


        results = []


        for query in queries:

            query_results = (
                await search_provider.search_companies(
                    query=query,
                    limit=8,
                )
            )


            for result in query_results:

                if any(
                    existing.url ==
                    result.url

                    for existing in results
                ):
                    continue

                results.append(
                    result
                )


        if not results:
            return CompanyContactSearchResponse(
                contacts=[],
                model="",
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

            "country":
                request.country,

            "city":
                request.city,

            "industry":
                request.industry,

            "language":
                request.language,

            "research_summary":
                request.research_summary,
        }


        instructions = dedent("""
You identify real business contacts for the
AllStockControl AI Sales system.

The contacts must work for the supplied company.

TARGET ROLES, in priority order:

1. Operations leadership
2. Warehouse or inventory management
3. Supply chain leadership
4. Logistics leadership
5. COO / Head of Operations
6. Owner, Founder or CEO only when a more
   operational contact cannot be supported.

STRICT RULES:

- Use only the supplied search evidence.
- Never invent a person.
- Never invent a job title.
- Never invent an email address.
- Never infer an email pattern.
- Never invent or infer a phone number.
- Never invent a LinkedIn URL.
- Never assume someone still works for the
  company unless the supplied evidence supports it.
- A contact must be clearly connected to the
  target company.
- Prefer contacts whose role is relevant to
  inventory, warehousing, logistics, operations,
  distribution or supply chain.
- Return at most 3 contacts.
- Do not return generic company contact pages as
  people.
- If no real person can be supported by the
  evidence, return an empty contacts list.

SOURCE:

- source_index must reference the supplied search
  result that supports that person.
- Never invent source indexes.
- Use only valid supplied indexes.

RELEVANCE SCORE:

- relevance_score represents how appropriate the
  person's role is for an AllStockControl sales
  conversation.
- 0 means not relevant.
- 100 means highly relevant.
- Score the ROLE relevance, not how likely the
  person is to buy.
""").strip()


        user_input = dedent(f"""
COMPANY:

{json.dumps(
    company_context,
    ensure_ascii=False,
    indent=2,
)}

SEARCH RESULTS:

{json.dumps(
    result_data,
    ensure_ascii=False,
    indent=2,
)}

Identify up to 3 verified contacts for this
company.
""").strip()


        analysis = (
            await ai_provider.generate_structured(
                instructions=instructions,
                user_input=user_input,
                response_model=
                    CompanyContactAnalysis,
            )
        )


        contacts = []


        sorted_contacts = sorted(
            analysis.data.contacts,
            key=lambda contact:
                contact.relevance_score,
            reverse=True,
        )


        for contact in sorted_contacts:

            if (
                contact.source_index < 0
                or contact.source_index >= len(
                    results
                )
            ):
                continue


            source_url = (
                results[
                    contact.source_index
                ].url
            )


            contacts.append(
                CompanyContactResult(
                    first_name=
                        contact.first_name,

                    last_name=
                        contact.last_name,

                    job_title=
                        contact.job_title,

                    email=
                        contact.email,

                    phone=
                        contact.phone,

                    linkedin_url=
                        contact.linkedin_url,

                    language=
                        contact.language,

                    source_url=
                        source_url,

                    source_type=
                        self.source_type(
                            source_url,
                            request.domain,
                        ),

                    relevance_score=
                        contact.relevance_score,

                    is_primary=
                        len(
                            contacts
                        ) == 0,
                )
            )


            if len(contacts) >= 3:
                break


        return CompanyContactSearchResponse(
            contacts=
                contacts,

            model=
                analysis.model,
        )