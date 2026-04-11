---
description: Create detailed implementation ticket(s) from a feature description
argument-hint: <feature description>
---

# Create Ticket: $ARGUMENTS

You are creating one or more implementation tickets based on the user's feature description. The goal is to produce tickets detailed enough that `/ticket` can implement them without ambiguity. Follow this structured workflow:

## Phase 1: Research

Thorough codebase research is critical — tickets that reference wrong patterns or miss existing infrastructure cause implementation friction.

1. Read recently completed tickets in `docs/tickets/complete/` to understand the ticket format, level of detail, and conventions (these are your format exemplars)
2. Read the relevant `docs/` reference files and `CLAUDE.md` files for the areas involved
3. Identify the existing codebase patterns most relevant to the feature. The user's description will often reference analogous systems (e.g., "like the inventory system" or "similar to how achievements work"). Thoroughly read those analogous systems — their modules, components, data structures, helpers — so you can mirror their patterns accurately in the ticket
4. Identify any enums, services, or infrastructure that the new feature will need to touch or extend
5. Check for naming conventions, file organization patterns, and architectural constraints

## Phase 2: Scope

Determine how many tickets are needed. The user may describe one feature or several related features. Consider:

- **One ticket per deployable unit of work**: Each ticket should be implementable and testable on its own
- **Dependency ordering**: If ticket B depends on ticket A's artifacts, make that explicit in B's Prerequisites section
- **User guidance**: The user may suggest how to split the work — follow their lead

Present the proposed ticket split to the user and confirm before writing.

## Phase 3: Clarify

Ask the user about anything that would block writing a precise ticket:

- **Ambiguities**: Requirements that could be interpreted multiple ways
- **Design choices**: Decisions that affect the ticket structure (e.g., dialog vs. page, new entity vs. extending existing)
- **Scope boundaries**: What's in vs. out for each ticket
- **Data/content questions**: Specific values, names, or configurations needed

Keep questions focused and concrete. Don't ask about things you can reasonably infer from the codebase patterns you've already read.

## Phase 4: Write Tickets

Write each ticket to `docs/tickets/<ticket-name>.md` (no date prefix — dates are added when tickets are completed).

### Ticket Shape

**Ticket shape varies by task type.** Match the format to the work, not the other way around:

- **Implementation tickets** — the full template below (numbered steps, files, test plan). Use a flat `## Implementation` section with numbered steps; group logically (e.g., "Model changes", "Service changes", "Component changes") when it helps clarity.
- **Design tickets** (e.g., hero progression, naming decisions) — simpler format: Summary, Context, design-specific sections, and a minimal test plan.
- **Refactoring tickets** — flat `## Implementation` with numbered steps across the whole stack.

### Implementation Ticket Template

```markdown
# Ticket Title

## Summary
1-2 sentences describing the change at a high level.

## Context
**Current behavior**: What exists today.
**New behavior**: What will exist after implementation.

## Prerequisites
- List any tickets, features, or artifacts that must exist before this ticket can be implemented
- Omit this section entirely if there are no prerequisites

## Acceptance Criteria
- [ ] What must be true when this ticket is done — behavioral, scope-defining assertions (e.g., "Users can filter by date range", "Settings persist across sessions")
- [ ] Each criterion should be unambiguously pass/fail

## Scope
Brief summary of the areas touched (e.g., "new data model + service layer, extends the notification system, adds settings UI"). Exact file paths belong in the implementation steps where they have context — this section is a quick-scan overview of blast radius.

## Implementation
### 1. Step Title
Detailed implementation instructions referencing specific files, patterns, and analogous code to mirror. Lead with *why* this step is needed, then point to where and what to change.
### 2. Next Step Title
...

## Test Plan
- [ ] How to verify the acceptance criteria — mechanical, action-oriented steps (e.g., "Open settings, change theme, reload — confirm theme persists", "Create item with empty name, confirm validation error")
```

### Writing Guidelines

- **Lead with intent, follow with location**: For each implementation step, state *why* the change is needed before *where* to make it. The "why" survives refactors; the "where" is a convenience that may drift.
- **Be specific about what, directional about how**: Name the exact files, entities, and enums involved. For method signatures and field types, point to the existing method/entity to mirror rather than spelling out the full signature — the implementer will read the current code, and transcribed signatures go stale.
- **Mirror existing patterns by reference**: When the ticket calls for something analogous to existing code, name the specific exemplar and call out only the *differences* from it. Don't transcribe the full structure — it may have changed by implementation time.
- **Keep scope tight**: Each ticket should be one coherent unit of work. If you find yourself writing a ticket that feels too large, consider splitting it.
- **Don't over-specify presentation**: Give enough structure (component names, layout sketches, behavioral descriptions) for the implementer to build it, but don't dictate every styling detail or exact positioning.
- **File paths live in implementation steps**: Reference specific files where the implementer will need them — inside the step that explains *why* the change is needed. Don't duplicate paths into a separate section where they lose context and go stale.
- **Assume the implementer verifies**: Tickets are read alongside the current codebase. Provide enough detail to orient the implementer (which files, which patterns, which analogues), but don't try to be a frozen snapshot of the code.

## Phase 5: Review

After writing, do a quick self-check:

- Does each ticket's Prerequisites section accurately list what must exist first?
- Would an implementer using `/ticket` have enough detail to orient themselves without re-reading the analogous systems?
- Does each step lead with intent (why) before location (where)?
- Are the tickets ordered so that dependencies flow forward (no circular prerequisites)?
- Are the acceptance criteria unambiguously pass/fail, and distinct from the test plan steps?

Present the completed tickets to the user with a brief summary of each.
