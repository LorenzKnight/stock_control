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


class CompanySearchCriteria(BaseModel):
    search_id: str = Field(
        min_length=1
    )

    market: str | None = None
    country: str | None = None
    city: str | None = None
    industry: str | None = None
    language: str | None = None

    limit: int = Field(
        default=10,
        ge=1,
        le=50
    )


class CompanySuggestion(BaseModel):
    name: str = Field(
        min_length=1
    )

    website: str | None = None
    country: str | None = None
    note: str | None = None


class CompanyDiscoveryRequest(BaseModel):
    searches: list[CompanySearchCriteria]

    suggestions: list[
        CompanySuggestion
    ] = Field(
        default_factory=list
    )


class CompanyCandidate(BaseModel):
    name: str
    website: str | None = None
    domain: str | None = None
    country: str | None = None
    city: str | None = None
    industry: str | None = None

    source_url: str
    source_text: str | None = None

    source_type: str

    search_id: str | None = None

    suggested_by_user: bool = False


class CompanyDiscoveryResponse(BaseModel):
    companies: list[
        CompanyCandidate
    ]


class CompanyAnalysisItem(BaseModel):
    result_index: int = Field(
        ge=0
    )

    is_company: bool

    matches_search: bool

    company_name: str = Field(
        min_length=1
    )

    industry: str | None = None

    city: str | None = None

    rejection_reason: str | None = None


class CompanyAnalysisResponse(BaseModel):
    results: list[
        CompanyAnalysisItem
    ]


class CompanyResearchRequest(BaseModel):
    name: str = Field(
        min_length=1
    )

    website: str | None = None
    domain: str | None = None

    market: str | None = None
    country: str | None = None
    city: str | None = None
    industry: str | None = None
    language: str | None = None


class CompanyResearchAnalysis(BaseModel):
    summary: str = Field(
        min_length=1
    )

    inventory_context: str | None = None

    city: str | None = None
    industry: str | None = None
    language: str | None = None

    source_indexes: list[int] = Field(
        min_length=1
    )


class CompanyResearchResponse(BaseModel):
    summary: str

    inventory_context: str | None = None

    city: str | None = None
    industry: str | None = None
    language: str | None = None

    sources: list[str]

    model: str