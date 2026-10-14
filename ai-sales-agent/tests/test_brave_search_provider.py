import os
import unittest

from unittest.mock import (
    AsyncMock,
    MagicMock,
    patch,
)

from app.search.brave_search_provider import (
    BraveSearchProvider,
)


class BraveSearchProviderTest(
    unittest.IsolatedAsyncioTestCase
):

    def testRejectsMissingApiKey(
        self
    ) -> None:

        with patch.dict(
            os.environ,
            {
                "BRAVE_SEARCH_API_KEY": ""
            },
        ):
            with self.assertRaisesRegex(
                RuntimeError,
                "BRAVE_SEARCH_API_KEY"
            ):
                BraveSearchProvider()


    async def testReturnsStructuredSearchResults(
        self
    ) -> None:

        response = MagicMock()

        response.status_code = 200

        response.json.return_value = {
            "web": {
                "results": [
                    {
                        "title":
                            "Example Logistics",

                        "url":
                            "https://example.com",

                        "description":
                            "Logistics company.",
                    }
                ]
            }
        }


        client = AsyncMock()

        client.get.return_value = (
            response
        )


        client_context = AsyncMock()

        client_context.__aenter__.return_value = (
            client
        )

        client_context.__aexit__.return_value = (
            None
        )


        with patch.dict(
            os.environ,
            {
                "BRAVE_SEARCH_API_KEY":
                    "test-key"
            },
        ):
            provider = (
                BraveSearchProvider()
            )


        with patch(
            (
                "app.search."
                "brave_search_provider."
                "httpx.AsyncClient"
            ),
            return_value=client_context,
        ):
            results = await provider.search_companies(
                query=(
                    "Logistics "
                    "Dominican Republic "
                    "company"
                ),
                limit=10,
            )


        self.assertEqual(
            len(results),
            1
        )

        self.assertEqual(
            results[0].title,
            "Example Logistics"
        )

        self.assertEqual(
            results[0].url,
            "https://example.com"
        )

        self.assertEqual(
            results[0].snippet,
            "Logistics company."
        )