import os

import httpx

from .base import (
    CompanySearchProvider,
    SearchResult,
)


class BraveSearchProvider(
    CompanySearchProvider
):

    def __init__(
        self
    ) -> None:

        self.api_key = os.getenv(
            "BRAVE_SEARCH_API_KEY",
            ""
        ).strip()

        if self.api_key == "":
            raise RuntimeError(
                "BRAVE_SEARCH_API_KEY "
                "is not configured."
            )


        self.base_url = (
            "https://api.search.brave.com"
            "/res/v1/web/search"
        )


    async def search_companies(
        self,
        query: str,
        limit: int,
    ) -> list[SearchResult]:

        if query.strip() == "":
            raise ValueError(
                "Search query cannot be empty."
            )


        count = min(
            max(
                limit,
                1
            ),
            20
        )


        headers = {
            "Accept":
                "application/json",

            "X-Subscription-Token":
                self.api_key,
        }


        params = {
            "q":
                query,

            "count":
                count,

            "safesearch":
                "moderate",
        }


        async with httpx.AsyncClient(
            timeout=20.0
        ) as client:

            response = await client.get(
                self.base_url,
                headers=headers,
                params=params,
            )


        if response.status_code < 200 \
                or response.status_code >= 300:

            raise RuntimeError(
                "Brave Search API returned "
                f"HTTP {response.status_code}."
            )


        try:
            data = response.json()

        except ValueError as error:
            raise RuntimeError(
                "Brave Search API returned "
                "invalid JSON."
            ) from error


        web = data.get(
            "web",
            {}
        )

        results = web.get(
            "results",
            []
        )


        if not isinstance(
            results,
            list
        ):
            return []


        search_results = []


        for result in results:

            title = str(
                result.get(
                    "title",
                    ""
                )
            ).strip()

            url = str(
                result.get(
                    "url",
                    ""
                )
            ).strip()

            description = str(
                result.get(
                    "description",
                    ""
                )
            ).strip()


            if title == "" or url == "":
                continue


            search_results.append(
                SearchResult(
                    title=title,
                    url=url,
                    snippet=(
                        description
                        or None
                    ),
                )
            )


        return search_results