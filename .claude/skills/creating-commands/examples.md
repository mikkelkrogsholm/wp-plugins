# Slash Command Examples

This file contains detailed, real-world examples of effective slash commands.

## Example 1: Simple Git Commit

```yaml
---
description: Create git commit with message
argument-hint: [message]
allowed-tools: Bash(git:*)
---

Create a git commit:

Message: $ARGUMENTS

Steps:
1. Stage all changes: `git add .`
2. Commit with message
3. Show commit hash and summary
```

**Usage**: `/commit Added user authentication feature`

**What it demonstrates**:
- Using $ARGUMENTS for flexible input
- Specific tool permissions (git only)
- Clear step-by-step instructions

## Example 2: Test Runner

```yaml
---
description: Run specific test file
argument-hint: [test-file]
allowed-tools: Bash(pytest:*)
---

Run tests in: $1

!`pytest $1 -v`

Analyze failures and suggest fixes.
```

**Usage**: `/test-run tests/test_auth.py`

**What it demonstrates**:
- Using $1 for positional argument
- Bash execution with `!` prefix
- Analysis after execution

## Example 3: Context-Heavy Command

```yaml
---
description: Prepare for code review
allowed-tools: Bash(git:*), Read
---

## Code Review Preparation

### Changes
!`git diff main..HEAD --stat`

### Commits
!`git log main..HEAD --oneline`

### Modified Files
!`git diff main..HEAD --name-only`

Summarize changes for code review and suggest review focus areas.
```

**Usage**: `/review-prep`

**What it demonstrates**:
- Multiple bash commands for context gathering
- Structured output sections
- No arguments needed (operates on current state)

## Example 4: Branching Workflow

```yaml
---
description: Create feature branch from issue
argument-hint: [issue-number]
allowed-tools: Bash(gh:*), Bash(git:*)
---

## Issue #$1 Details

!`gh issue view $1 --json title,body,labels`

## Task

1. Create branch: `issue-$1-{short-description}`
2. Switch to branch
3. Print next steps for working on this issue
```

**Usage**: `/start-issue 123`

**What it demonstrates**:
- Integration with GitHub CLI (gh)
- Using issue data to inform branch creation
- Multi-tool permissions (gh + git)

## Example 5: Multi-Step Workflow

```yaml
---
description: Complete issue and create PR
argument-hint: [issue-number]
allowed-tools: Bash(git:*), Bash(gh:*)
---

## Current Status

Branch: !`git branch --show-current`
Status: !`git status --short`

## Task

Complete issue #$1 workflow:
1. Ensure all changes committed
2. Push branch to origin
3. Create PR linking to issue #$1
4. Provide PR URL
```

**Usage**: `/finish-issue 123`

**What it demonstrates**:
- Context awareness (current branch/status)
- Complete workflow automation
- Clear deliverable (PR URL)

## Example 6: Command That Activates a Skill

```yaml
---
description: Run comprehensive code review
---

Activate the code-review-process Skill and review the current changes.
```

**Usage**: `/code-review`

**What it demonstrates**:
- **Thin wrapper pattern** - command activates skill
- Skill contains the actual review process
- Command is just a user-friendly trigger

## Example 7: Context + Skill Activation

```yaml
---
description: Deploy to production with safety checks
allowed-tools: Bash(git:*)
---

## Current State

Branch: !`git branch --show-current`
Last commit: !`git log -1 --oneline`

## Task

Activate the deployment-workflow Skill and deploy to production.
Ensure all safety checks pass before deployment.
```

**Usage**: `/deploy`

**What it demonstrates**:
- Gathering context before delegating to skill
- Skill handles the complex deployment logic
- Command provides current state context

## When to Use Each Pattern

### Pattern 1: Simple Action (Example 1, 2)
**Use when**: Single, straightforward operation
- Quick git operations
- Running tests
- File operations

### Pattern 2: Context Gathering (Example 3)
**Use when**: Need multiple pieces of information before acting
- Code review preparation
- Status summaries
- Pre-deployment checks

### Pattern 3: Workflow Automation (Example 4, 5)
**Use when**: Multi-step process with clear sequence
- Branch creation workflows
- Issue-to-PR automation
- Release processes

### Pattern 4: Skill Activation (Example 6, 7)
**Use when**: Complex logic that should live in a skill
- Code review (complex process)
- Deployment (many steps, decisions)
- Any process that might evolve over time

## Tips for Writing Good Examples

1. **Show Real Usage**: Include actual command invocations
2. **Explain the Why**: What does this pattern demonstrate?
3. **Be Specific**: Real tool names, real file paths
4. **Show Output**: What does the user get back?
5. **Demonstrate Patterns**: Each example should teach a technique
