import os
import secrets

from fastapi import (
    FastAPI,
    Header,
    HTTPException,
)

from .company_discovery_service import (
    CompanyDiscoveryService,
)
from .sales_agent_service import (
    SalesAgentService,
)
from .schemas import (
    CompanyDiscoveryRequest,
    CompanyDiscoveryResponse,
    CompanyResearchRequest,
    CompanyResearchResponse,
    SalesAgentRequest,
    SalesAgentResponse,
    CompanyQualificationRequest,
    CompanyQualificationResponse,
    CompanyContactSearchRequest,
    CompanyContactSearchResponse,
)
from .company_research_service import (
    CompanyResearchService,
)
from .company_qualification_service import (
    CompanyQualificationService,
)
from .company_contact_service import (
    CompanyContactService,
)


app = FastAPI(
    title="AllStockControl AI Sales Agent",
    version="1.0.0",
)


def validate_agent_token(
    authorization: str | None,
) -> None:
    expected_token = os.getenv(
        "AI_SALES_AGENT_TOKEN",
        "",
    ).strip()

    if expected_token == "":
        return

    if not authorization:
        raise HTTPException(
            status_code=401,
            detail="Missing authorization token.",
        )

    prefix = "Bearer "

    if not authorization.startswith(prefix):
        raise HTTPException(
            status_code=401,
            detail="Invalid authorization token.",
        )

    provided_token = authorization[
        len(prefix):
    ].strip()

    if not secrets.compare_digest(
        provided_token,
        expected_token,
    ):
        raise HTTPException(
            status_code=401,
            detail="Invalid authorization token.",
        )


@app.get("/health")
async def health() -> dict[str, str]:
    return {
        "status": "ok"
    }


@app.post(
    "/generate-reply",
    response_model=SalesAgentResponse,
)
async def generate_reply(
    request: SalesAgentRequest,
    authorization: str | None = Header(
        default=None
    ),
) -> SalesAgentResponse:

    validate_agent_token(
        authorization
    )

    if request.task != "generate_sales_reply":
        raise HTTPException(
            status_code=400,
            detail="Unsupported AI Sales task.",
        )

    try:
        service = SalesAgentService()

        return await service.generate_reply(
            request
        )

    except Exception as error:
        raise HTTPException(
            status_code=500,
            detail=str(error),
        ) from error



@app.post(
    "/generate-outreach",
    response_model=SalesAgentResponse,
)
async def generate_outreach(
    request: SalesAgentRequest,
    authorization: str | None = Header(
        default=None
    ),
) -> SalesAgentResponse:

    validate_agent_token(
        authorization
    )

    if request.task != "generate_first_outreach":
        raise HTTPException(
            status_code=400,
            detail="Unsupported AI Sales task.",
        )

    try:
        service = SalesAgentService()

        return await service.generate_outreach(
            request
        )

    except Exception as error:
        raise HTTPException(
            status_code=500,
            detail=str(error),
        ) from error


    
@app.post(
    "/discover-companies",
    response_model=CompanyDiscoveryResponse,
)
async def discover_companies(
    request: CompanyDiscoveryRequest,
    authorization: str | None = Header(
        default=None
    ),
) -> CompanyDiscoveryResponse:

    validate_agent_token(
        authorization
    )

    try:
        service = CompanyDiscoveryService()

        return await service.find_companies(
            request
        )

    except Exception as error:
        raise HTTPException(
            status_code=500,
            detail=str(error),
        ) from error


@app.post(
    "/research-company",
    response_model=CompanyResearchResponse,
)
async def research_company(
    request: CompanyResearchRequest,
    authorization: str | None = Header(
        default=None
    ),
) -> CompanyResearchResponse:

    validate_agent_token(
        authorization
    )

    try:
        service = CompanyResearchService()

        return await service.research_company(
            request
        )

    except Exception as error:
        raise HTTPException(
            status_code=500,
            detail=str(error),
        ) from error



@app.post(
    "/qualify-company",
    response_model=CompanyQualificationResponse,
)
async def qualify_company(
    request: CompanyQualificationRequest,
    authorization: str | None = Header(
        default=None
    ),
) -> CompanyQualificationResponse:

    validate_agent_token(
        authorization
    )

    try:
        service = CompanyQualificationService()

        return await service.qualify_company(
            request
        )

    except Exception as error:
        raise HTTPException(
            status_code=500,
            detail=str(error),
        ) from error



@app.post(
    "/find-company-contacts",
    response_model=CompanyContactSearchResponse,
)
async def find_company_contacts(
    request: CompanyContactSearchRequest,
    authorization: str | None = Header(
        default=None
    ),
) -> CompanyContactSearchResponse:

    validate_agent_token(
        authorization
    )

    try:
        service = CompanyContactService()

        return await service.find_contacts(
            request
        )

    except Exception as error:
        raise HTTPException(
            status_code=500,
            detail=str(error),
        ) from error