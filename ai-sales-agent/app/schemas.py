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

    missing_information: list[str] = Field(
        default_factory=list
    )

    requires_human_input: bool = Field(
        default=False
    )


class SalesAgentResponse(BaseModel):
    subject: str | None
    message: str
    model: str
    missing_information: list[str]
    requires_human_input: bool