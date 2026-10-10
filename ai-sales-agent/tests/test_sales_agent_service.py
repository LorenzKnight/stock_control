import unittest
from unittest.mock import (
    AsyncMock,
    patch,
)

from app.providers.base import AIProviderResult
from app.sales_agent_service import SalesAgentService
from app.schemas import (
    SalesAgentRequest,
    SalesReply,
)


class SalesAgentServiceTest(
    unittest.IsolatedAsyncioTestCase
):

    async def testGeneratesReplySubjectFromConversation(
        self
    ) -> None:
        provider = AsyncMock()

        provider.generate_reply.return_value = (
            AIProviderResult(
                reply=SalesReply(
                    subject=None,
                    message="Generated reply."
                ),
                model="test-model"
            )
        )

        request = SalesAgentRequest(
            task="generate_sales_reply",
            context={
                "conversation": {
                    "subject": "Inventory management solution"
                }
            }
        )

        with patch(
            "app.sales_agent_service.create_ai_provider",
            return_value=provider
        ):
            service = SalesAgentService()

            result = await service.generate_reply(
                request
            )

        self.assertEqual(
            result.subject,
            "Re: Inventory management solution"
        )

        self.assertEqual(
            result.message,
            "Generated reply."
        )

        self.assertEqual(
            result.model,
            "test-model"
        )


    async def testDoesNotDuplicateReplyPrefix(
        self
    ) -> None:
        provider = AsyncMock()

        provider.generate_reply.return_value = (
            AIProviderResult(
                reply=SalesReply(
                    subject=None,
                    message="Generated reply."
                ),
                model="test-model"
            )
        )

        request = SalesAgentRequest(
            task="generate_sales_reply",
            context={
                "conversation": {
                    "subject": "Re: Inventory management solution"
                }
            }
        )

        with patch(
            "app.sales_agent_service.create_ai_provider",
            return_value=provider
        ):
            service = SalesAgentService()

            result = await service.generate_reply(
                request
            )

        self.assertEqual(
            result.subject,
            "Re: Inventory management solution"
        )


    async def testUsesProviderSubjectWhenConversationHasNoSubject(
        self
    ) -> None:
        provider = AsyncMock()

        provider.generate_reply.return_value = (
            AIProviderResult(
                reply=SalesReply(
                    subject="Generated subject",
                    message="Generated reply."
                ),
                model="test-model"
            )
        )

        request = SalesAgentRequest(
            task="generate_sales_reply",
            context={}
        )

        with patch(
            "app.sales_agent_service.create_ai_provider",
            return_value=provider
        ):
            service = SalesAgentService()

            result = await service.generate_reply(
                request
            )

        self.assertEqual(
            result.subject,
            "Generated subject"
        )


    async def testPassesInstructionsAndContextToProvider(
        self
    ) -> None:
        provider = AsyncMock()

        provider.generate_reply.return_value = (
            AIProviderResult(
                reply=SalesReply(
                    subject=None,
                    message="Generated reply."
                ),
                model="test-model"
            )
        )

        request = SalesAgentRequest(
            task="generate_sales_reply",
            context={
                "product": {
                    "name": "AllStockControl",
                    "verified_facts": [
                        (
                            "AllStockControl helps companies "
                            "manage inventory."
                        )
                    ]
                },
                "company": {
                    "name": "Demo Logistics AB"
                }
            }
        )

        with patch(
            "app.sales_agent_service.create_ai_provider",
            return_value=provider
        ):
            service = SalesAgentService()

            await service.generate_reply(
                request
            )

        provider.generate_reply.assert_awaited_once()

        call = provider.generate_reply.await_args

        instructions = (
            call.kwargs["instructions"]
        )

        user_input = (
            call.kwargs["user_input"]
        )

        self.assertIn(
            "VERIFIED PRODUCT FACTS",
            instructions
        )

        self.assertIn(
            "AllStockControl helps companies manage inventory.",
            user_input
        )

        self.assertIn(
            "Demo Logistics AB",
            user_input
        )


if __name__ == "__main__":
    unittest.main()