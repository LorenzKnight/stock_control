import json

from textwrap import dedent

from .providers.factory import (
    create_ai_provider,
)
from .schemas import (
    CompanyQualificationAnalysis,
    CompanyQualificationRequest,
    CompanyQualificationResponse,
)


class CompanyQualificationService:

    def qualification_level(
        self,
        score: int,
    ) -> str:

        if score >= 70:
            return "HIGH"

        if score >= 40:
            return "MEDIUM"

        return "LOW"


    async def qualify_company(
        self,
        request: CompanyQualificationRequest,
    ) -> CompanyQualificationResponse:

        provider = (
            create_ai_provider()
        )


        company_context = {
            "name":
                request.name,

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

            "research_summary":
                request.research_summary,

            "inventory_context":
                request.inventory_context,
        }


        instructions = dedent("""
You qualify sales prospects for AllStockControl.

AllStockControl is an inventory and stock management
system.

Score the company from 0 to 100 using ONLY the
supplied company research.

SCORING RUBRIC:

1. Inventory / stock relevance: 0-40 points

Evaluate whether the evidence supports operations
involving inventory, stock, physical goods,
warehousing, storage, fulfilment, distribution,
branches, logistics, products, or supply chain.

2. Operational complexity: 0-25 points

Evaluate supported operational complexity such as
multiple locations, warehousing, distribution,
logistics operations, movement of physical goods,
or other processes that could require structured
inventory control.

3. AllStockControl applicability: 0-25 points

Evaluate how naturally inventory management
software could apply to the supported operations.

Do not claim the company currently needs new
software.
Do not claim it has problems that are not in the
evidence.

4. Evidence quality: 0-10 points

Score how strong and specific the supplied research
is for making the qualification.

STRICT RULES:

- Use only the supplied evidence.
- Never invent business problems.
- Never invent company size, revenue, employees,
  systems, customers, warehouses, or technology.
- Do not assume a company needs AllStockControl.
- Lack of evidence should reduce the score.
- reason must explain the score briefly and
  factually.
- The final score must be between 0 and 100.
""").strip()


        user_input = dedent(f"""
COMPANY RESEARCH:

{json.dumps(
    company_context,
    ensure_ascii=False,
    indent=2,
)}

Score this company as a potential AllStockControl
sales prospect.
""").strip()


        analysis = (
            await provider.generate_structured(
                instructions=instructions,
                user_input=user_input,
                response_model=
                    CompanyQualificationAnalysis,
            )
        )


        score = (
            analysis.data.score
        )


        return CompanyQualificationResponse(
            score=score,

            level=
                self.qualification_level(
                    score
                ),

            reason=
                analysis.data.reason,

            model=
                analysis.model,
        )