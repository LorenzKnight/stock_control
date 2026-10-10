from typing import Any

from pydantic import BaseModel, Field


class SalesAgentRequest(BaseModel):
    task: str
    context: dict[str, Any]


class SalesReply(BaseModel):
    subject: str | None = Field(
        default=None
    )

    message: str = Field(
        min_length=1
    )


class SalesAgentResponse(BaseModel):
    subject: str | None
    message: str
    model: str