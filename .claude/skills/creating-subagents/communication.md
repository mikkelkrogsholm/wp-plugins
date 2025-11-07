# Subagent Communication and Testing

This file covers how subagents communicate with skills, other subagents, and how to test them effectively.

## Hooks-Based Chaining (November 2025 Pattern)

The recommended way to chain subagents is now **hooks**, not prompt-based delegation.

### Why Hooks Over Prompts

**Old Way** (Prompt-Based):
```markdown
## When to Delegate
If tests fail → Suggest `test-runner` subagent
```

Problem: Requires human to read suggestion and manually invoke.

**New Way** (Hooks-Based):
```yaml
# .claude/hooks/subagent-stop.yaml
- name: pm-to-architect-handoff
  when: |
    subagent.name == "pm-spec" and
    status in output contains "READY_FOR_ARCH"
  action: |
    print("Spec complete. Run: /architect-review")
    # Or auto-trigger next agent (if configured)
```

Benefits: Deterministic automation, clear pipeline, human approval points.

### Hooks for Subagent Chaining

**SubagentStop Hook**: Triggers when subagent completes
**Stop Hook**: Triggers at conversation end
**PreToolUse Hook**: Can validate before subagent spawn

### Pattern: Pipeline with Hooks

```
PM-Spec Agent completes
    ↓ (SubagentStop hook)
Hook reads status from output
    ↓ (if status == READY_FOR_ARCH)
Hook prints: "Run: /architect-review"
    ↓ (human approval)
Human executes suggested command
    ↓
Architect-Review Agent starts
```

### Implementation

1. Create `.claude/hooks/subagent-stop.yaml`
2. Define conditions for handoff
3. Print suggested next command
4. Human executes (HITL pattern)

### Best Practices

- **Print Suggestions**: Don't auto-execute, suggest commands
- **Status-Based**: Read status fields from subagent output
- **Clear Handoffs**: Each stage prints "Next: ..."
- **Queue Files**: Hooks can read/write queue files for state

See official hooks documentation for full syntax and examples.

## Invoking Skills from Subagents

Subagents can leverage Skills for specialized knowledge, creating a composable system.

### Basic Pattern

```markdown
## Your Process

1. Check code style guidelines
2. **Invoke the `code-style-guide` Skill** for language-specific patterns
3. Apply recommendations
4. Report violations
```

### Why This Works

- **Separation of Concerns**: Subagent handles the workflow, Skill provides knowledge
- **Reusability**: Multiple subagents can use the same skill
- **Maintainability**: Update skill once, all subagents benefit
- **Context Management**: Skills load only when explicitly invoked

### Example: Code Reviewer Using Skills

```yaml
---
name: code-reviewer
description: Reviews code using project-specific guidelines
tools: Read, Grep, Glob
---

# Code Reviewer

## Your Process

1. Read the changed files using git diff
2. **Invoke the `python-style-guide` Skill** if reviewing Python code
3. **Invoke the `security-checklist` Skill** for security review
4. Analyze code against the loaded guidelines
5. Return structured JSON output with findings

## When to Invoke Skills

- Python code → `python-style-guide`
- JavaScript code → `javascript-style-guide`
- Any code → `security-checklist`
- API endpoints → `api-design-patterns`
```

## Invoking Other Subagents (Delegation)

Subagents can suggest delegating to other subagents for specialized tasks.

### Delegation Pattern

```markdown
## When to Delegate

If you encounter:
- Test failures → Suggest `test-runner` subagent
- Deployment tasks → Suggest `deployment-engineer` subagent
- Security issues → Suggest `security-reviewer` subagent
```

### Why Delegation Matters

- **Context Isolation**: Each subagent gets its own context window
- **Specialization**: Right expert for the job
- **Token Management**: Prevents context pollution
- **Parallel Work**: Multiple subagents can work concurrently

### Example: Orchestration Subagent

```yaml
---
name: feature-implementer
description: Implements complete features by coordinating multiple specialists
---

# Feature Implementer

You coordinate feature implementation by delegating to specialists.

## Your Workflow

1. **Analyze** the feature requirements
2. **Design** the implementation approach
3. **Delegate** to appropriate subagents:
   - Code writing → `backend-engineer` subagent
   - Tests → `test-generator` subagent
   - Code review → `code-reviewer` subagent
   - Documentation → `docs-writer` subagent
4. **Aggregate** results from all subagents
5. **Report** final status to user

## Delegation Guidelines

- Always explain WHY you're delegating
- Provide clear context to the next subagent
- Aggregate structured outputs (JSON) for final report
```

## Communication Anti-Patterns

### Anti-Pattern 1: Circular Dependencies

❌ **Bad**:
```
subagent-a → calls → subagent-b → calls → subagent-a
```

This creates infinite loops and context confusion.

✅ **Good**:
```
orchestrator → delegates → specialist-a
orchestrator → delegates → specialist-b
(specialists don't call each other)
```

### Anti-Pattern 2: Skill Overload

❌ **Bad**:
```markdown
## Your Process
1. Invoke skill-1
2. Invoke skill-2
3. Invoke skill-3
4. Invoke skill-4
5. Invoke skill-5
```

Loading too many skills wastes context.

✅ **Good**:
```markdown
## Your Process
1. Analyze the code language
2. Invoke ONLY the relevant style guide skill
3. Apply guidelines
```

## Testing Subagents

### Method 1: Explicit Invocation

Test by directly calling the subagent:

```
User: "Use the code-reviewer subagent to check my recent changes"
```

**What to verify**:
- Subagent activates correctly
- Has access to required tools
- Returns expected output format
- Structured output (JSON) is valid

### Method 2: Automatic Invocation

Test that descriptions trigger correctly:

```
User: "I just modified the authentication code"
```

If `code-reviewer` description says "Use PROACTIVELY after code changes", it should activate automatically.

**What to verify**:
- Subagent detects the trigger keywords
- Activates without explicit user request
- Appropriate for the context

### Method 3: Workflow Testing

Test complete workflows with multiple subagents:

```
User: "Implement a new user registration feature"
```

Expected flow:
1. `feature-planner` creates implementation plan
2. `backend-engineer` writes the code
3. `test-generator` creates tests
4. `code-reviewer` reviews everything
5. Main orchestrator aggregates results

**What to verify**:
- Correct subagent sequence
- Context properly passed between subagents
- Structured outputs properly aggregated
- Final report is comprehensive

## Testing Checklist

Before deploying a new subagent:

- [ ] Test explicit invocation
- [ ] Test automatic triggering (if PROACTIVE)
- [ ] Verify tool access works
- [ ] Check structured output format (JSON)
- [ ] Test with edge cases
- [ ] Verify error handling
- [ ] Check delegation patterns work
- [ ] Test skill invocation
- [ ] Verify context isolation
- [ ] Check token usage is reasonable

## Debugging Communication Issues

### Issue: Subagent Not Invoked

**Symptoms**:
- User expects subagent to activate, but it doesn't
- Manual invocation works, but automatic doesn't

**Debug Steps**:
1. Check description field - are trigger keywords clear?
2. Verify "Use when..." or "Use PROACTIVELY when..." present
3. Test with explicit trigger words
4. Check for conflicts with other subagent descriptions

**Fix**:
```yaml
# Weak trigger
description: Code reviewer

# Strong trigger
description: Expert code reviewer. Use PROACTIVELY after writing or modifying code. Reviews quality, security, and maintainability.
```

### Issue: Wrong Subagent Activated

**Symptoms**:
- Different subagent than expected activates
- Multiple subagents try to activate simultaneously

**Debug Steps**:
1. Check description overlap between subagents
2. Look for ambiguous trigger keywords
3. Make descriptions more specific

**Fix**:
```yaml
# Ambiguous
description: Helps with backend code

# Specific
description: Python FastAPI backend developer. Use for REST API endpoints, database models, and SQLAlchemy queries.
```

### Issue: Subagent Can't Access Tools

**Symptoms**:
- Subagent tries to use a tool but gets permission error
- Workflow fails at tool execution

**Debug Steps**:
1. Check `tools:` field in YAML frontmatter
2. Verify tool names are spelled correctly
3. Check if main conversation allows those tools

**Fix**:
```yaml
# Missing tools
---
name: code-analyzer
description: Analyzes code
# No tools field - might inherit wrong ones
---

# Explicit tools
---
name: code-analyzer
description: Analyzes code
tools: Read, Grep, Glob
---
```

## Best Practices for Communication

1. **Clear Interfaces**: Define what each subagent expects as input and provides as output
2. **Structured Outputs**: Always use JSON for machine-readable results
3. **Document Dependencies**: List which skills or subagents this one works with
4. **Avoid Coupling**: Subagents shouldn't know implementation details of others
5. **Use Orchestrators**: Have a coordinator that delegates, not peer-to-peer calls
6. **Test Isolation**: Each subagent should work independently
7. **Context Efficiency**: Don't load unnecessary skills or data
