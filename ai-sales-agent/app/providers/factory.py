import os

from .base import AIProvider
from .openai_provider import OpenAIProvider
from .openrouter_provider import OpenRouterProvider


def create_ai_provider() -> AIProvider:

    provider = os.getenv(
        "AI_PROVIDER",
        "openrouter",
    ).strip().lower()


    if provider == "openrouter":
        return OpenRouterProvider()


    if provider == "openai":
        return OpenAIProvider()


    raise RuntimeError(
        f"Unsupported AI provider: {provider}"
    )