from abc import ABC, abstractmethod
from dataclasses import dataclass

from ..schemas import SalesReply


@dataclass
class AIProviderResult:
    reply: SalesReply
    model: str


class AIProvider(ABC):

    @abstractmethod
    async def generate_reply(
        self,
        instructions: str,
        user_input: str,
    ) -> AIProviderResult:
        pass