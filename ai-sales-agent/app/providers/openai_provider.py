import os

from openai import AsyncOpenAI

from .base import (
    AIProvider,
    AIProviderResult,
)
from ..schemas import SalesReply


class OpenAIProvider(AIProvider):

    def __init__(self) -> None:
        self.model = os.getenv(
            "OPENAI_MODEL",
            "gpt-6-luna",
        )

        api_key = os.getenv(
            "OPENAI_API_KEY",
            "",
        ).strip()

        if api_key == "":
            raise RuntimeError(
                "OPENAI_API_KEY is not configured."
            )

        self.client = AsyncOpenAI(
            api_key=api_key
        )


    async def generate_reply(
        self,
        instructions: str,
        user_input: str,
    ) -> AIProviderResult:

        response = await self.client.responses.parse(
            model=self.model,
            instructions=instructions,
            input=user_input,
            text_format=SalesReply,
        )

        reply = response.output_parsed

        if reply is None:
            raise RuntimeError(
                "OpenAI returned no structured sales reply."
            )

        return AIProviderResult(
            reply=reply,
            model=response.model or self.model,
        )