import os
import secrets

from fastapi import (
    FastAPI,
    Header,
    HTTPException,
)

from .sales_agent_service import SalesAgentService
from .schemas import (
    SalesAgentRequest,
    SalesAgentResponse,
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