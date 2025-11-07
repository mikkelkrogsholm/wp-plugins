# Slash Command Patterns

This file describes the six most common and effective command patterns.

## Pattern 1: Simple Action Commands

**Purpose**: Execute a single, straightforward operation

```yaml
---
description: Run all tests
allowed-tools: Bash(pytest:*)
---

Run the complete test suite:
!`pytest -v`

Report results and any failures.
```

**Characteristics**:
- No or minimal arguments
- Single tool execution
- Quick feedback
- Clear success/failure outcome

**Good For**:
- Running tests
- Building projects
- Formatting code
- Quick status checks

**When NOT to Use**:
- Complex multi-step workflows
- Processes requiring decisions
- Tasks needing extensive context

## Pattern 2: Context Gathering Commands

**Purpose**: Collect and present information about current state

```yaml
---
description: Analyze current work
allowed-tools: Bash(git:*)
---

## Current Work Context

- Branch: !`git branch --show-current`
- Status: !`git status --short`
- Recent commits: !`git log --oneline -5`
- Uncommitted changes: !`git diff HEAD --stat`

Summarize what I'm currently working on.
```

**Characteristics**:
- Multiple bash commands for context
- Structured information presentation
- Analysis/summarization after gathering
- No state modification (read-only)

**Good For**:
- Code review preparation
- Work status summaries
- Pre-deployment checks
- Project health reports

**When NOT to Use**:
- When immediate action is needed
- Simple single-fact queries
- Heavy computation (use subagent instead)

## Pattern 3: Workflow Commands

**Purpose**: Automate multi-step sequences with clear order

```yaml
---
description: Create feature branch from issue
argument-hint: [issue-number]
allowed-tools: Bash(gh:*), Bash(git:*)
---

## Issue Details

!`gh issue view $1 --json title,body`

## Task

1. Create branch: `issue-$1-{description}`
2. Check out the branch
3. Summarize the issue for me
```

**Characteristics**:
- Multiple sequential steps
- Often takes arguments (issue #, feature name)
- Combines context gathering with action
- Clear workflow progression

**Good For**:
- Issue-to-branch workflows
- Release processes
- PR creation automation
- Feature scaffolding

**When NOT to Use**:
- Complex decision logic (use skill instead)
- Steps that might fail (need error handling)
- Processes that evolve frequently (use skill)

## Pattern 4: Validation Commands

**Purpose**: Run checks before important operations

```yaml
---
description: Validate code before commit
allowed-tools: Bash(git:*), Bash(npm:*), Bash(pytest:*)
---

## Pre-Commit Checks

1. Run linter: !`npm run lint`
2. Run tests: !`pytest`
3. Check types: !`npm run type-check`
4. Git status: !`git status`

Report any issues that need fixing before commit.
```

**Characteristics**:
- Multiple validation steps
- Pass/fail gates
- Comprehensive reporting
- No state changes (just checks)

**Good For**:
- Pre-commit validation
- Pre-deployment checks
- Code quality gates
- Build verification

**When NOT to Use**:
- When checks are slow (use subagent for parallelization)
- Need for conditional logic
- Auto-fixing issues (that's a separate command)

## Pattern 5: Generation Commands

**Purpose**: Create new code, files, or structures

```yaml
---
description: Generate API endpoint
argument-hint: [resource-name]
---

Create a complete REST API endpoint for: $1

Include:
- Model definition
- Schema (Create, Update, Response)
- CRUD operations
- Endpoints (GET, POST, PUT, DELETE)
- Tests
```

**Characteristics**:
- Takes descriptive input
- Creates multiple files/components
- Follows project conventions
- Often uses templates

**Good For**:
- Boilerplate generation
- Scaffolding new features
- Creating test files
- Component creation

**When NOT to Use**:
- Complex generation logic (use skill + scripts)
- Context-dependent patterns (skill is better)
- Evolving templates (maintain in skills)

## Pattern 6: Skill Activation Commands (Thin Wrappers)

**Purpose**: Provide user-friendly trigger for complex skills

```yaml
---
description: Run comprehensive code review
---

Activate the code-review-process Skill and review the current changes.
```

**Characteristics**:
- Minimal command file (2-3 lines)
- Delegates to skill immediately
- May gather context first
- Process lives in skill, not command

**Good For**:
- Complex workflows
- Processes with decision logic
- Frequently evolving processes
- Multi-stage operations

**When NOT to Use**:
- Simple operations (Pattern 1 is better)
- When skill doesn't exist yet
- One-off commands

## Pattern 7: Context Pollution Prevention (Delegation Pattern)

**Purpose**: Avoid filling main thread with massive noisy outputs by delegating to subagents

### The Problem

Research (August 2025) shows slash commands executed directly cause **context pollution**:
- 91% of output is noise (test logs, file dumps, verbose output)
- 169k tokens consumed for simple operations
- Main conversation becomes unreadable

### The Solution

Commands that generate massive output should immediately delegate to subagents:

```yaml
---
description: Analyze test coverage across entire codebase
---

This operation generates extensive output. Delegating to subagent...

Use the test-analyzer subagent to:
1. Run coverage report
2. Analyze all modules
3. Return distilled summary only

The subagent will return key findings without polluting main context.
```

### When to Delegate (Decision Framework)

**Use Direct Command** when:
- Output < 5k tokens
- Result is brief and contextually relevant
- Output is needed in main conversation
- Example: `/git-status`, `/quick-test`, `/format-file`

**Delegate to SubAgent** when:
- Output > 5k tokens (test logs, parsing large files)
- Exploring large codebases
- Generating extensive reports
- Need isolation for quality
- Example: `/deep-analysis`, `/full-test-suite`, `/security-audit`

### Token Economics

**Direct Command (Context Pollution)**:
- Input: 2k tokens (command + context)
- Output: 167k tokens (full test logs)
- Noise: 91% (153k tokens wasted)
- Main thread: Polluted, hard to read

**SubAgent Delegation**:
- Input: 2k tokens (command triggers subagent)
- SubAgent context: 23k tokens (isolated)
- SubAgent output: 2k tokens (distilled summary)
- Main thread: Clean, 21k tokens vs 169k (8× savings)

### Key Principle

"**Parallel reads work well, coordinated writes required**"

- **Parallel Reads**: Multiple subagents exploring independently (use delegation)
- **Coordinated Writes**: Main thread orchestrates changes (keep in commands)

### Example: Preventing Pollution

**❌ Bad - Direct Execution:**
```yaml
---
description: Run full test suite
---

!`pytest --verbose --coverage`

[... 150k tokens of test output floods main thread ...]
```

**✅ Good - Delegation:**
```yaml
---
description: Run full test suite
---

Use test-runner subagent to execute full test suite.

The subagent will:
1. Run all tests in isolated context
2. Analyze failures
3. Return summary: pass/fail counts, critical failures, recommendations

This prevents 150k+ tokens of test logs from polluting main conversation.
```

## Combining Patterns

### Pattern Combination Example

```yaml
---
description: Complete feature implementation
argument-hint: [feature-name]
allowed-tools: Bash(git:*)
---

## Context (Pattern 2: Gathering)
Current branch: !`git branch --show-current`
Status: !`git status --short`

## Validation (Pattern 4: Checks)
Run pre-implementation checks:
- Is branch clean?
- Are we on correct base branch?

## Workflow (Pattern 6: Skill Activation)
If checks pass, activate the feature-implementation Skill with feature: $1

The skill will handle:
- Code generation
- Test creation
- Documentation updates
```

This combines:
- Context gathering (Pattern 2)
- Validation (Pattern 4)
- Skill delegation (Pattern 6)

## Pattern Selection Guide

```
Start here: What's the command's main purpose?

├─ Execute single operation? → Pattern 1 (Simple Action)
├─ Gather information? → Pattern 2 (Context Gathering)
├─ Multi-step sequence? → Pattern 3 (Workflow)
├─ Check before action? → Pattern 4 (Validation)
├─ Generate code? → Pattern 5 (Generation)
├─ Complex process? → Pattern 6 (Skill Activation)
└─ Generates massive output (>5k tokens)? → Pattern 7 (Delegation)
```

## Anti-Patterns

### Anti-Pattern: The Monolithic Command

❌ **Bad** - Entire process in command:
```yaml
---
description: Deploy to production
---

[200 lines of deployment steps, error handling, rollback logic...]
```

✅ **Good** - Thin wrapper to skill:
```yaml
---
description: Deploy to production
---

Activate the deployment-workflow Skill and deploy to production environment.
```

### Anti-Pattern: The Do-Everything Command

❌ **Bad** - Multiple unrelated functions:
```yaml
description: Developer tools
---
If $1 is "test", run tests
If $1 is "build", run build
If $1 is "deploy", run deploy
[100 lines of conditionals...]
```

✅ **Good** - Separate commands:
- `/test` - Run tests
- `/build` - Build project
- `/deploy` - Deploy application

## Pattern Evolution

Commands often evolve through these stages:

1. **Stage 1: Simple Action**
   ```yaml
   /test → runs pytest
   ```

2. **Stage 2: Context + Action**
   ```yaml
   /test → shows git status, then runs pytest
   ```

3. **Stage 3: Workflow**
   ```yaml
   /test → status, run tests, analyze failures, suggest fixes
   ```

4. **Stage 4: Skill Delegation**
   ```yaml
   /test → activates testing-workflow skill (complex logic moved)
   ```

**When to Evolve**: If your command grows beyond 50 lines or needs conditional logic, consider moving to next stage.
