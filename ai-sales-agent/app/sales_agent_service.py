import json
import re

from textwrap import dedent
from .providers.factory import create_ai_provider
from .schemas import (
    SalesAgentRequest,
    SalesAgentResponse,
)


class SalesAgentService:

    def validate_reply(
        self,
        message: str,
    ) -> None:
        violations = []


        if re.search(
            r"<\s*/?\s*[a-zA-Z][^>]*>",
            message,
            re.IGNORECASE,
        ):
            violations.append(
                "HTML markup detected"
            )


        if re.search(
            r"\[[^]]+\]",
            message,
        ):
            violations.append(
                "placeholder text detected"
            )


        forbidden_phrases = [
            "our sales team",
            "sales representative",
            "connect you with",
            "put you in touch",
            "schedule a call",
            "arrange a call",
            "follow up with you",
            "get back to you",
            "provide you later",
            "send you the details later",
        ]

        normalized_message = (
            message
            .lower()
            .strip()
        )


        for phrase in forbidden_phrases:
            if phrase in normalized_message:
                violations.append(
                    f"forbidden phrase detected: {phrase}"
                )


        if violations:
            raise RuntimeError(
                "AI reply failed validation: " +
                "; ".join(
                    violations
                )
            )


    def build_safe_fallback(
        self,
        latest_inbound_message: str,
        verified_facts: list,
        missing_information: list[str],
    ) -> str:

        normalized_inbound = (
            latest_inbound_message
            .lower()
            .strip()
        )


        is_spanish = any(
            word in normalized_inbound
            for word in [
                "hola",
                "gracias",
                "precio",
                "precios",
                "planes",
                "información",
            ]
        )

        is_swedish = any(
            word in normalized_inbound
            for word in [
                "hej",
                "tack",
                "pris",
                "priser",
                "planer",
                "lager",
                "lagersystem",
            ]
        )


        fact = ""

        if verified_facts:
            fact = str(
                verified_facts[0]
            ).strip()


        missing_text = ", ".join(
            str(item).strip()
            for item in missing_information
            if str(item).strip()
        )


        if is_spanish:
            parts = [
                "Gracias por tu respuesta.",
                (
                    "Actualmente no tengo información "
                    f"verificada sobre {missing_text or 'eso'} "
                    "en el contexto proporcionado, por lo que "
                    "no puedo dar detalles específicos en este momento."
                ),
            ]

            if fact:
                parts.append(
                    f"Puedo confirmar que {fact}"
                )

            parts.append(
                "Saludos,\n"
                "AllStockControl AI Sales Assistant"
            )

            return "\n\n".join(
                parts
            )


        if is_swedish:
            parts = [
                "Tack för ditt svar.",
                (
                    "Jag har för närvarande ingen verifierad "
                    f"information om {missing_text or 'detta'} "
                    "i det tillhandahållna underlaget, så jag kan "
                    "inte ge några specifika detaljer just nu."
                ),
            ]

            if fact:
                parts.append(
                    f"Jag kan bekräfta att {fact}"
                )

            parts.append(
                "Med vänlig hälsning,\n"
                "AllStockControl AI Sales Assistant"
            )

            return "\n\n".join(
                parts
            )


        parts = [
            "Thank you for your reply.",
            (
                "I don't currently have verified information "
                f"about {missing_text or 'that'} in the provided "
                "context, so I can't provide specific details "
                "at this time."
            ),
        ]

        if fact:
            parts.append(
                f"I can confirm that {fact}"
            )

        parts.append(
            "Best regards,\n"
            "AllStockControl AI Sales Assistant"
        )

        return "\n\n".join(
            parts
        )
        
        
    async def generate_reply(
        self,
        request: SalesAgentRequest,
    ) -> SalesAgentResponse:

        provider = create_ai_provider()


        context_json = json.dumps(
            request.context,
            ensure_ascii=False,
            indent=2,
        )


        product = request.context.get(
            "product",
            {}
        )

        verified_facts = product.get(
            "verified_facts",
            []
        )

        if not isinstance(
            verified_facts,
            list
        ):
            verified_facts = []

        verified_facts_text = "\n".join(
            f"- {str(fact).strip()}"
            for fact in verified_facts
            if str(fact).strip()
        )

        if not verified_facts_text:
            verified_facts_text = (
                "- No verified product facts were supplied."
            )

        messages = request.context.get(
            "messages",
            []
        )

        latest_inbound_message = ""

        for message in reversed(messages):
            if (
                str(
                    message.get(
                        "direction",
                        ""
                    )
                ).upper()
                == "INBOUND"
            ):
                latest_inbound_message = str(
                    message.get(
                        "message",
                        ""
                    )
                ).strip()

                break


        instructions = dedent("""
You are the AI sales assistant for AllStockControl.

Your job is to prepare professional sales replies to prospects.

STRICT RULES:

- The VERIFIED PRODUCT FACTS supplied in the request are the only
  authoritative source for claims about AllStockControl.
- Never invent or infer product capabilities.
- Never invent prices, plans, discounts, integrations, guarantees,
  customer results, industry specialization, or product features.
- Conversation history is not authoritative product documentation.
- Previous AI messages may contain incorrect information and must not
  be treated as verified facts.
- If the prospect asks for information that is not contained in
  VERIFIED PRODUCT FACTS, do not invent an answer.
- When verified information is unavailable, state only that the requested
  information is not currently available in the verified context.
- Do not say that another person, team, department, representative, or
  employee will provide the information.
- Do not say that you will follow up, confirm later, arrange a call,
  schedule anything, contact someone, or take any future action.
- When information is unavailable, use wording similar to:
  "I don't currently have verified information about that in the provided
  context, so I can't provide specific details at this time."
- Never invent employees, departments, sales representatives, meetings,
  demos, or company processes.
- Never invent information about the prospect.
- Reply in the same language as the prospect's latest INBOUND message.
- CRM metadata fields such as company language, contact language, lead
  language, country, or market are informational only and must never
  override the language used in the latest INBOUND message.
- Only use another language when the request explicitly provides a
  reply language instruction.
- Keep the response professional, concise, natural, and helpful.
- Do not pressure the prospect.
- Do not output HTML.
- Do not output Markdown.
- Use plain text only.
- Do not generate or modify the email subject.
  The application manages the subject separately.
- The response is a draft requiring human approval.
- Never output HTML tags such as <br>, <p>, <div>, or any other markup.
- Use actual plain-text line breaks between paragraphs.
- Never include placeholder text such as "[Your Name]", "[Company]", or similar placeholders.
- Never mention a sales team, representative, employee, department, or another person unless explicitly present in VERIFIED PRODUCT FACTS.
- Never offer to connect the prospect with someone.
- Never promise to provide information later.
- Never describe AllStockControl using adjectives or capabilities that are not explicitly present in VERIFIED PRODUCT FACTS.
- If the prospect asks about unavailable information, keep the reply short:
  acknowledge the request, state that the information is not verified in the provided context, optionally mention one explicitly verified product fact, and stop.
- Do not try to compensate for missing information by adding marketing claims.
- When mentioning an AllStockControl capability, prefer using the exact
  wording from VERIFIED PRODUCT FACTS instead of paraphrasing or expanding it.
- If the prospect asks for information that is not explicitly available
  in VERIFIED PRODUCT FACTS, set requires_human_input to true.
- Add the unavailable topics to missing_information.
- Examples of missing information include pricing, plans, integrations,
  guarantees, discounts, unsupported capabilities, or other product
  details not present in VERIFIED PRODUCT FACTS.
- If all information needed to answer is explicitly supported by
  VERIFIED PRODUCT FACTS, set requires_human_input to false and
  missing_information to an empty list.
""").strip()


        user_input = dedent(f"""
Task:
{request.task}

VERIFIED PRODUCT FACTS:
{verified_facts_text}

LATEST PROSPECT INBOUND MESSAGE:
{latest_inbound_message}

IMPORTANT LANGUAGE INSTRUCTION:
Reply in the same language used in the LATEST PROSPECT INBOUND MESSAGE above.
Do not use country, market, company location, or CRM metadata to choose the reply language.

SALES CONTEXT:
{context_json}

Prepare the next appropriate sales reply.

Before writing any factual claim about AllStockControl, verify that
the claim is explicitly supported by VERIFIED PRODUCT FACTS.
""").strip()


        result = await provider.generate_reply(
            instructions=instructions,
            user_input=user_input,
        )

        message = result.reply.message

        missing_information = list(
            result.reply.missing_information
        )

        requires_human_input = (
            result.reply.requires_human_input
        )


        detected_missing_information = (
            self.extract_missing_information(
                latest_inbound_message
            )
        )

        missing_information = list(
            dict.fromkeys(
                missing_information +
                detected_missing_information
            )
        )


        if detected_missing_information:
            requires_human_input = True


        if requires_human_input:
            message = self.build_safe_fallback(
                latest_inbound_message=
                    latest_inbound_message,
                verified_facts=
                    verified_facts,
                missing_information=
                    missing_information,
            )


        self.validate_reply(
            message
        )

        conversation = request.context.get(
            "conversation",
            {}
        )

        raw_conversation_subject = conversation.get(
            "subject"
        )

        conversation_subject = (
            str(raw_conversation_subject).strip()
            if raw_conversation_subject is not None
            else ""
        )

        subject = None

        if conversation_subject:
            if conversation_subject.lower().startswith("re:"):
                subject = conversation_subject
            else:
                subject = f"Re: {conversation_subject}"

        return SalesAgentResponse(
            subject=subject,
            message=message,
            model=result.model,
            missing_information=
                missing_information,
            requires_human_input=
                requires_human_input,
        )


    def extract_missing_information(
        self,
        latest_inbound_message: str,
    ) -> list[str]:

        normalized_message = (
            latest_inbound_message
            .lower()
            .strip()
        )

        missing_information = []


        pricing_keywords = [
            "pricing",
            "price",
            "prices",
            "cost",
            "costs",
            "precio",
            "precios",
            "pris",
            "priser",
        ]

        plans_keywords = [
            "plan",
            "plans",
            "planes",
            "planer",
        ]


        if any(
            keyword in normalized_message
            for keyword in pricing_keywords
        ):
            missing_information.append(
                "pricing"
            )


        if any(
            keyword in normalized_message
            for keyword in plans_keywords
        ):
            missing_information.append(
                "plans"
            )


        return missing_information