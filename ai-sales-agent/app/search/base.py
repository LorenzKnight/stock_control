from abc import (
    ABC,
    abstractmethod,
)
from dataclasses import dataclass


@dataclass
class SearchResult:
    title: str
    url: str
    snippet: str | None = None


class CompanySearchProvider(ABC):

    @abstractmethod
    async def search_companies(
        self,
        query: str,
        limit: int,
    ) -> list[SearchResult]:
        pass