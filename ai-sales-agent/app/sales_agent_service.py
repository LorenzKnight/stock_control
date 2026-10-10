import json

from textwrap import dedent
from .providers.factory import create_ai_provider
from .schemas import (
    SalesAgentRequest,
    SalesAgentResponse,
)


class SalesAgentService:

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
- Match the language used by the prospect unless another language is
  explicitly specified.
- Keep the response professional, concise, natural, and helpful.
- Do not pressure the prospect.
- Do not output HTML.
- Do not output Markdown.
- Use plain text only.
- Use normal line breaks for paragraphs.
- Do not generate or modify the email subject.
  The application manages the subject separately.
- The response is a draft requiring human approval.
""").strip()


        user_input = dedent(f"""
Task:
{request.task}

VERIFIED PRODUCT FACTS:
{verified_facts_text}

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

        conversation = request.context.get(
            "conversation",
            {}
        )

        conversation_subject = str(
            conversation.get(
                "subject",
                ""
            )
        ).strip()

        subject = result.reply.subject

        if conversation_subject:
            if conversation_subject.lower().startswith(
                "re:"
            ):
                subject = conversation_subject
            else:
                subject = (
                    f"Re: {conversation_subject}"
                )

        return SalesAgentResponse(
            subject=subject,
            message=message,
            model=result.model,
        )