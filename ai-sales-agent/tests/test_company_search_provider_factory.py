import os
import unittest

from unittest.mock import patch

from app.search.brave_search_provider import (
    BraveSearchProvider,
)
from app.search.factory import (
    create_company_search_provider,
)


class CompanySearchProviderFactoryTest(
    unittest.TestCase
):

    def testCreatesBraveProvider(
        self
    ) -> None:

        with patch.dict(
            os.environ,
            {
                "COMPANY_SEARCH_PROVIDER":
                    "brave",

                "BRAVE_SEARCH_API_KEY":
                    "test-key",
            },
        ):
            provider = (
                create_company_search_provider()
            )


        self.assertIsInstance(
            provider,
            BraveSearchProvider
        )


    def testRejectsUnsupportedProvider(
        self
    ) -> None:

        with patch.dict(
            os.environ,
            {
                "COMPANY_SEARCH_PROVIDER":
                    "unknown"
            },
        ):
            with self.assertRaisesRegex(
                RuntimeError,
                "Unsupported company search provider"
            ):
                create_company_search_provider()