from abc import ABC, abstractmethod
from dataclasses import dataclass

from ..schemas import SalesReply

from typing import (
    Generic,
    TypeVar,
)

from pydantic import BaseModel


@dataclass
class AIProviderResult:
    reply: SalesReply
    model: str


T = TypeVar(
    "T",
    bound=BaseModel,
)


@dataclass
class AIStructuredResult(
    Generic[T]
):
    data: T
    model: str


class AIProvider(ABC):

    @abstractmethod
    async def generate_reply(
        self,
        instructions: str,
        user_input: str,
    ) -> AIProviderResult:
        pass

    @abstractmethod
    async def generate_structured(
        self,
        instructions: str,
        user_input: str,
        response_model: type[T],
    ) -> AIStructuredResult[T]:
        pass