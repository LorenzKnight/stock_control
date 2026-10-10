import os
import unittest
from unittest.mock import patch

from app.providers.factory import create_ai_provider
from app.providers.openai_provider import OpenAIProvider
from app.providers.openrouter_provider import OpenRouterProvider


class ProviderFactoryTest(
    unittest.TestCase
):

    def testCreatesOpenRouterProvider(
        self
    ) -> None:
        with patch.dict(
            os.environ,
            {
                "AI_PROVIDER": "openrouter",
                "OPENROUTER_API_KEY": "test-openrouter-key",
                "OPENROUTER_MODEL": "openrouter/free"
            },
            clear=False
        ):
            provider = create_ai_provider()

        self.assertIsInstance(
            provider,
            OpenRouterProvider
        )


    def testCreatesOpenAIProvider(
        self
    ) -> None:
        with patch.dict(
            os.environ,
            {
                "AI_PROVIDER": "openai",
                "OPENAI_API_KEY": "test-openai-key",
                "OPENAI_MODEL": "test-model"
            },
            clear=False
        ):
            provider = create_ai_provider()

        self.assertIsInstance(
            provider,
            OpenAIProvider
        )


    def testDefaultsToOpenRouterProvider(
        self
    ) -> None:
        environment = {
            key: value
            for key, value in os.environ.items()
            if key != "AI_PROVIDER"
        }

        environment[
            "OPENROUTER_API_KEY"
        ] = "test-openrouter-key"

        with patch.dict(
            os.environ,
            environment,
            clear=True
        ):
            provider = create_ai_provider()

        self.assertIsInstance(
            provider,
            OpenRouterProvider
        )


    def testRejectsUnsupportedProvider(
        self
    ) -> None:
        with patch.dict(
            os.environ,
            {
                "AI_PROVIDER": "unknown-provider"
            },
            clear=False
        ):
            with self.assertRaisesRegex(
                RuntimeError,
                "Unsupported AI provider"
            ):
                create_ai_provider()


if __name__ == "__main__":
    unittest.main()