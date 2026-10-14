import os

from openai import AsyncOpenAI

from .base import (
    AIProvider,
    AIProviderResult,
    AIStructuredResult,
    T,
)
from ..schemas import SalesReply

from pydantic import BaseModel


class OpenRouterProvider(AIProvider):

    def __init__(self) -> None:
        self.model = os.getenv(
            "OPENROUTER_MODEL",
            "openrouter/free",
        )

        api_key = os.getenv(
            "OPENROUTER_API_KEY",
            "",
        ).strip()

        if api_key == "":
            raise RuntimeError(
                "OPENROUTER_API_KEY is not configured."
            )

        self.client = AsyncOpenAI(
            api_key=api_key,
            base_url="https://openrouter.ai/api/v1",
        )


    async def generate_reply(
        self,
        instructions: str,
        user_input: str,
    ) -> AIProviderResult:

        response = await self.client.chat.completions.create(
            model=self.model,

            messages=[
                {
                    "role": "system",
                    "content": instructions,
                },
                {
                    "role": "user",
                    "content": user_input,
                },
            ],

            response_format={
                "type": "json_schema",
                "json_schema": {
                    "name": "sales_reply",
                    "strict": True,
                    "schema": SalesReply.model_json_schema(),
                },
            },

            extra_body={
                "provider": {
                    "require_parameters": True
                }
            },
        )


        content = (
            response.choices[0]
            .message
            .content
        )

        if not content:
            raise RuntimeError(
                "OpenRouter returned an empty sales reply."
            )


        try:
            reply = SalesReply.model_validate_json(
                content
            )

        except Exception as error:
            raise RuntimeError(
                "OpenRouter returned an invalid structured sales reply."
            ) from error


        return AIProviderResult(
            reply=reply,
            model=response.model or self.model,
        )


    async def generate_structured(
        self,
        instructions: str,
        user_input: str,
        response_model: type[T],
    ) -> AIStructuredResult[T]:

        response = (
            await self.client.chat.completions.create(
                model=self.model,
                messages=[
                    {
                        "role": "system",
                        "content": instructions,
                    },
                    {
                        "role": "user",
                        "content": user_input,
                    },
                ],
                response_format={
                    "type": "json_schema",
                    "json_schema": {
                        "name":
                            "structured_response",

                        "strict":
                            True,

                        "schema":
                            response_model
                            .model_json_schema(),
                    },
                },
                extra_body={
                    "provider": {
                        "require_parameters":
                            True
                    }
                },
            )
        )


        content = (
            response.choices[0]
            .message
            .content
        )

        if not content:
            raise RuntimeError(
                "OpenRouter returned an "
                "empty structured response."
            )


        try:
            data = (
                response_model
                .model_validate_json(
                    content
                )
            )

        except Exception as error:
            raise RuntimeError(
                "OpenRouter returned an invalid "
                "structured response."
            ) from error


        return AIStructuredResult(
            data=data,
            model=response.model or self.model,
        )