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


    async def testReturnsNullSubjectWhenConversationHasNoSubject(
        self,
    ) -> None:
        provider = AsyncMock()

        provider.generate_reply.return_value = AIProviderResult(
            reply=SalesReply(
                subject="Provider generated subject",
                message="Generated reply.",
            ),
            model="test-model",
        )

        with patch(
            "app.sales_agent_service.create_ai_provider",
            return_value=provider,
        ):
            service = SalesAgentService()

            response = await service.generate_reply(
                SalesAgentRequest(
                    task="generate_sales_reply",
                    context={
                        "conversation": {
                            "subject": None
                        },
                        "product": {
                            "verified_facts": []
                        }
                    },
                )
            )

        self.assertIsNone(
            response.subject
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

    async def testUsesSafeFallbackWhenHumanInputIsRequired(
        self
    ) -> None:
        provider = AsyncMock()

        provider.generate_reply.return_value = (
            AIProviderResult(
                reply=SalesReply(
                    subject=None,
                    message=(
                        "I can connect you with our sales team "
                        "<br>[Your Name]"
                    ),
                    missing_information=[
                        "pricing",
                        "plans",
                    ],
                    requires_human_input=True,
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
                            "manage inventory and stock operations "
                            "from one system."
                        )
                    ]
                },
                "conversation": {
                    "subject": (
                        "Inventory management solution "
                        "for Demo Logistics AB"
                    )
                },
                "messages": [
                    {
                        "direction": "INBOUND",
                        "sender_type": "CONTACT",
                        "message": (
                            "Could you send me some information "
                            "about pricing and available plans?"
                        ),
                    }
                ],
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


        self.assertTrue(
            result.requires_human_input
        )

        self.assertEqual(
            result.missing_information,
            [
                "pricing",
                "plans",
            ]
        )

        self.assertIn(
            "I don't currently have verified information",
            result.message
        )

        self.assertIn(
            "pricing, plans",
            result.message
        )

        self.assertIn(
            (
                "AllStockControl helps companies manage "
                "inventory and stock operations from one system."
            ),
            result.message
        )


        self.assertNotIn(
            "our sales team",
            result.message
        )

        self.assertNotIn(
            "<br>",
            result.message
        )

        self.assertNotIn(
            "[Your Name]",
            result.message
        )

    async def testUsesProviderMessageWhenHumanInputIsNotRequired(
        self
    ) -> None:
        provider = AsyncMock()

        provider.generate_reply.return_value = (
            AIProviderResult(
                reply=SalesReply(
                    subject=None,
                    message=(
                        "Thank you for your reply. "
                        "AllStockControl helps companies "
                        "manage inventory."
                    ),
                    missing_information=[],
                    requires_human_input=False,
                ),
                model="test-model"
            )
        )

        request = SalesAgentRequest(
            task="generate_sales_reply",
            context={
                "product": {
                    "verified_facts": [
                        (
                            "AllStockControl helps companies "
                            "manage inventory."
                        )
                    ]
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


        self.assertFalse(
            result.requires_human_input
        )

        self.assertEqual(
            result.missing_information,
            []
        )

        self.assertEqual(
            result.message,
            (
                "Thank you for your reply. "
                "AllStockControl helps companies "
                "manage inventory."
            )
        )

    async def testRejectsInvalidProviderMessageWhenNoFallbackIsUsed(
        self
    ) -> None:
        provider = AsyncMock()

        provider.generate_reply.return_value = (
            AIProviderResult(
                reply=SalesReply(
                    subject=None,
                    message=(
                        "I can connect you with our sales team."
                    ),
                    missing_information=[],
                    requires_human_input=False,
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

            with self.assertRaisesRegex(
                RuntimeError,
                "AI reply failed validation"
            ):
                await service.generate_reply(
                    request
                )


    async def testDetectsMissingInformationFromInboundMessage(
        self
    ) -> None:
        provider = AsyncMock()

        provider.generate_reply.return_value = (
            AIProviderResult(
                reply=SalesReply(
                    subject=None,
                    message="Generated reply.",
                    missing_information=[],
                    requires_human_input=False,
                ),
                model="test-model"
            )
        )

        request = SalesAgentRequest(
            task="generate_sales_reply",
            context={
                "messages": [
                    {
                        "direction": "INBOUND",
                        "message": (
                            "Could you send me information "
                            "about pricing and available plans?"
                        ),
                    }
                ],
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
            result.missing_information,
            [
                "pricing",
                "plans",
            ]
        )

        self.assertTrue(
            result.requires_human_input
        )

        self.assertIn(
            "pricing, plans",
            result.message
        )


    async def testGeneratesFirstOutreach(
        self
    ) -> None:
        provider = AsyncMock()

        provider.generate_reply.return_value = (
            AIProviderResult(
                reply=SalesReply(
                    subject=(
                        "Inventory operations at Demo Logistics"
                    ),
                    message=(
                        "Hi Anna,\n\n"
                        "AllStockControl helps companies manage "
                        "inventory and stock operations from one system. "
                        "Based on the supplied company context, "
                        "inventory operations appear relevant to your role. "
                        "Would this be relevant to explore for your team?"
                    ),
                    missing_information=[],
                    requires_human_input=False,
                ),
                model="test-model"
            )
        )


        request = SalesAgentRequest(
            task="generate_first_outreach",
            context={
                "product": {
                    "name": "AllStockControl",
                    "verified_facts": [
                        (
                            "AllStockControl helps companies "
                            "manage inventory and stock operations "
                            "from one system."
                        )
                    ]
                },
                "company": {
                    "name": "Demo Logistics AB",
                    "industry": "Logistics",
                    "country": "Sweden",
                    "language": "en",
                    "research_summary": (
                        "Demo Logistics AB provides "
                        "logistics services."
                    ),
                    "inventory_context": (
                        "The company operates warehousing "
                        "and inventory-related activities."
                    )
                },
                "contact": {
                    "name": "Anna Svensson",
                    "job_title": "Operations Manager",
                    "language": "en"
                },
                "lead": {
                    "stage": "NEW",
                    "score": 82,
                    "score_reason": (
                        "Strong inventory relevance."
                    )
                },
                "conversation": {
                    "channel": "EMAIL"
                }
            }
        )


        with patch(
            "app.sales_agent_service.create_ai_provider",
            return_value=provider
        ):
            service = SalesAgentService()

            result = await service.generate_outreach(
                request
            )


        self.assertEqual(
            result.subject,
            "Inventory operations at Demo Logistics"
        )

        self.assertEqual(
            result.model,
            "test-model"
        )

        self.assertFalse(
            result.requires_human_input
        )

        self.assertEqual(
            result.missing_information,
            []
        )


        provider.generate_reply.assert_awaited_once()


    async def testFirstOutreachUsesConfiguredLanguage(
        self
    ) -> None:
        provider = AsyncMock()

        provider.generate_reply.return_value = (
            AIProviderResult(
                reply=SalesReply(
                    subject="Lagerhantering",
                    message=(
                        "Hej Anna. "
                        "AllStockControl hjälper företag att "
                        "hantera lager och lagerverksamhet."
                    ),
                ),
                model="test-model"
            )
        )


        request = SalesAgentRequest(
            task="generate_first_outreach",
            context={
                "contact": {
                    "language": "sv"
                },
                "conversation": {
                    "channel": "EMAIL"
                }
            }
        )


        with patch(
            "app.sales_agent_service.create_ai_provider",
            return_value=provider
        ):
            service = SalesAgentService()

            await service.generate_outreach(
                request
            )


        call = provider.generate_reply.await_args

        user_input = call.kwargs["user_input"]
        

        self.assertIn(
            "Swedish",
            user_input
        )


    async def testFirstOutreachRemovesSubjectForNonEmailChannel(
        self
    ) -> None:
        provider = AsyncMock()

        provider.generate_reply.return_value = (
            AIProviderResult(
                reply=SalesReply(
                    subject="Should not survive",
                    message=(
                        "Hi Anna, would inventory management "
                        "be relevant to your operations?"
                    ),
                ),
                model="test-model"
            )
        )


        request = SalesAgentRequest(
            task="generate_first_outreach",
            context={
                "conversation": {
                    "channel": "LINKEDIN"
                }
            }
        )


        with patch(
            "app.sales_agent_service.create_ai_provider",
            return_value=provider
        ):
            service = SalesAgentService()

            result = await service.generate_outreach(
                request
            )


        self.assertIsNone(
            result.subject
        )


    async def testFirstEmailOutreachRequiresSubject(
        self
    ) -> None:
        provider = AsyncMock()

        provider.generate_reply.return_value = (
            AIProviderResult(
                reply=SalesReply(
                    subject=None,
                    message="Hi Anna, is this relevant to your team?",
                ),
                model="test-model"
            )
        )


        request = SalesAgentRequest(
            task="generate_first_outreach",
            context={
                "conversation": {
                    "channel": "EMAIL"
                }
            }
        )


        with patch(
            "app.sales_agent_service.create_ai_provider",
            return_value=provider
        ):
            service = SalesAgentService()


            with self.assertRaisesRegex(
                RuntimeError,
                "AI outreach did not provide an email subject"
            ):
                await service.generate_outreach(
                    request
                )


if __name__ == "__main__":
    unittest.main()