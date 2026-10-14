import os

from .base import CompanySearchProvider
from .brave_search_provider import (
    BraveSearchProvider,
)


def create_company_search_provider(
) -> CompanySearchProvider:

    provider = os.getenv(
        "COMPANY_SEARCH_PROVIDER",
        ""
    ).strip().lower()

    if provider == "":
        raise RuntimeError(
            "COMPANY_SEARCH_PROVIDER "
            "is not configured."
        )

    if provider == "brave":
        return BraveSearchProvider()

    raise RuntimeError(
        f"Unsupported company search provider: "
        f"{provider}"
    )