import unittest

from unittest.mock import (
    AsyncMock,
    patch,
)

from app.company_qualification_service import (
    CompanyQualificationService,
)
from app.providers.base import (
    AIStructuredResult,
)
from app.schemas import (
    CompanyQualificationAnalysis,
    CompanyQualificationRequest,
)


class CompanyQualificationServiceTest(
    unittest.IsolatedAsyncioTestCase
):

    def testQualificationLevelUsesScoreThresholds(
        self
    ) -> None:

        service = (
            CompanyQualificationService()
        )


        cases = [
            (0, "LOW"),
            (39, "LOW"),
            (40, "MEDIUM"),
            (69, "MEDIUM"),
            (70, "HIGH"),
            (100, "HIGH"),
        ]


        for score, expected_level in cases:

            with self.subTest(
                score=score
            ):
                self.assertEqual(
                    service.qualification_level(
                        score
                    ),
                    expected_level
                )


    async def testQualifiesCompanyUsingResearchEvidence(
        self
    ) -> None:

        provider = AsyncMock()

        provider.generate_structured.return_value = (
            AIStructuredResult(
                data=CompanyQualificationAnalysis(
                    score=88,
                    reason=(
                        "The company has supported "
                        "warehousing, storage and "
                        "distribution operations."
                    ),
                ),
                model="test-model",
            )
        )


        request = CompanyQualificationRequest(
            name="DP World",
            market="LATAM",
            country="Dominican Republic",
            city="Caucedo",
            industry="Logistics",
            language="en",
            research_summary=(
                "DP World Dominican Republic "
                "provides integrated supply chain, "
                "freight forwarding and warehousing "
                "solutions."
            ),
            inventory_context=(
                "The company offers warehousing "
                "and storage services and manages "
                "the receipt, storage and "
                "distribution of goods."
            ),
        )


        with patch(
            (
                "app.company_qualification_service."
                "create_ai_provider"
            ),
            return_value=provider,
        ):
            service = (
                CompanyQualificationService()
            )

            response = (
                await service.qualify_company(
                    request
                )
            )


        provider.generate_structured.assert_awaited_once()


        call = (
            provider.generate_structured.await_args
        )

        user_input = (
            call.kwargs[
                "user_input"
            ]
        )

        self.assertIn(
            "DP World",
            user_input
        )

        self.assertIn(
            "warehousing",
            user_input
        )

        self.assertIn(
            "Caucedo",
            user_input
        )


        self.assertEqual(
            response.score,
            88
        )

        self.assertEqual(
            response.level,
            "HIGH"
        )

        self.assertEqual(
            response.reason,
            (
                "The company has supported "
                "warehousing, storage and "
                "distribution operations."
            )
        )

        self.assertEqual(
            response.model,
            "test-model"
        )


    async def testReturnsMediumQualificationLevel(
        self
    ) -> None:

        provider = AsyncMock()

        provider.generate_structured.return_value = (
            AIStructuredResult(
                data=CompanyQualificationAnalysis(
                    score=55,
                    reason=(
                        "Some inventory-related "
                        "operations are supported, "
                        "but evidence is limited."
                    ),
                ),
                model="test-model",
            )
        )


        request = CompanyQualificationRequest(
            name="Example Logistics",
            country="Dominican Republic",
            industry="Logistics",
            research_summary=(
                "The company provides "
                "logistics services."
            ),
            inventory_context=None,
        )


        with patch(
            (
                "app.company_qualification_service."
                "create_ai_provider"
            ),
            return_value=provider,
        ):
            service = (
                CompanyQualificationService()
            )

            response = (
                await service.qualify_company(
                    request
                )
            )


        self.assertEqual(
            response.score,
            55
        )

        self.assertEqual(
            response.level,
            "MEDIUM"
        )


if __name__ == "__main__":
    unittest.main()