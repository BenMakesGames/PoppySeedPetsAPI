---
description: Implement a ticket from docs/tickets/
argument-hint: <ticket-name>
---

# Implement Ticket: $ARGUMENTS

You are implementing a ticket from the unimplemented features list. Follow this structured workflow:

## Phase 1: Review

1. Locate the ticket matching "$ARGUMENTS" in the `docs/tickets/` directory
2. Read the relevant `docs/` reference files for the area you'll be working in. Reference docs exist in the workspace root (`docs/`)  and contain accumulated patterns and gotchas from previous tickets.
3. Read the relevant `CLAUDE.md` file(s)
4. Read all code files referenced in the ticket
5. Understand the current state of the implementation

## Phase 2: Check Prerequisites

If the ticket has a **Prerequisites** section, verify that each prerequisite is actually met before going further. Check concretely — look for the entities, files, features, endpoints, or other artifacts that the prerequisites describe. If any prerequisite is **not** met, stop and tell the user which prerequisites are unmet and why. Do not proceed to implementation. Let the user decide whether it makes sense to continue anyway.

## Phase 3: Clarify

Before implementing, identify and ask the user about:
- **Ambiguities**: Unclear requirements or missing details
- **Contradictions**: Conflicts between the ticket and existing code
- **Design decisions**: Choices that could go multiple ways
- **Gaps**: Missing information needed to implement

Present your findings and questions clearly. Wait for user responses before proceeding.

## Phase 4: Implement

Once requirements are clear:
1. Create a todo list to track implementation steps
2. Implement the feature following existing code patterns
3. Build and test frequently to catch errors early
4. Mark todos complete as you progress

## Phase 5: Verify

1. Run a final build/test pass to ensure no errors
2. Review the implementation for completeness

## Phase 6: Document

1. Move the completed ticket from `docs/tickets/` to `docs/tickets/complete/`, prefixing the filename with today's date in `yyyy-mm-dd` format (e.g., `2026-02-16 My Ticket.md`)
2. Add a "## Learnings" section to the bottom of the completed ticket documenting:
   - **Architectural decisions**: Design choices made during implementation and why (e.g., "Used X pattern instead of Y because...")
   - **Problems encountered**: Bugs, gotchas, or unexpected behavior you ran into and how they were resolved
   - **Interesting tidbits**: Non-obvious things learned about the codebase, frameworks, or domain that could be useful for future work
   - **Workarounds / limitations**: Framework constraints or quirks that required workarounds, and whether the workaround is permanent or could be revisited
   - **Related areas affected**: Other parts of the codebase that were unexpectedly touched or that may need future attention
   - **Rejected alternatives**: Approaches that were considered but not taken, and why — to prevent future work from hitting the same dead ends
3. If this ticket implements or changes a game design feature, update `docs/game-design.md` accordingly (add new sections, correct outdated descriptions, etc.). The game design doc is a living reference — it should always reflect the current state of the game's design.
4. Update the relevant `docs/` reference file(s) with any **reusable** patterns learned during this ticket. This is the key self-learning step -- distill what's generally useful (new patterns confirmed, gotchas discovered) vs. what's ticket-specific. Examples:
   - Discovered a new gotcha or recurring pattern? Add it to the relevant reference doc
   - New API convention worth documenting? Add to the appropriate docs
   - Don't duplicate the full ticket narrative -- just the distilled, reusable bits

## Important Notes

- Ask clarifying questions early, but also ask if new questions arise during implementation
- Use the todo list to track progress
- Keep the user informed of your progress
