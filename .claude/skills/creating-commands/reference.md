# Slash Command Reference

This file contains detailed technical information about bash execution, tool restrictions, testing, and troubleshooting.

## Bash Command Execution (Deep Dive)

### How Bash Execution Works

Commands prefixed with `!` execute BEFORE the prompt is sent to Claude. The output becomes part of the prompt context.

**Basic Pattern**:
```yaml
Context: !`command`
```

**Execution Flow**:
1. Command file is read
2. All `!` commands are identified
3. Bash commands execute (in order of appearance)
4. Output is captured
5. Output replaces `!`command`` in the prompt
6. Final prompt sent to Claude

### Multiple Command Patterns

#### Pattern 1: Sequential Context Gathering

```yaml
---
allowed-tools: Bash(git:*), Bash(docker:*)
---

## Context

- Git status: !`git status`
- Docker status: !`docker ps`
- Current branch: !`git branch --show-current`

## Task

Deploy the current branch.
```

**Result**: All three commands run, their outputs are inserted, then Claude processes.

#### Pattern 2: Conditional Logic (Limited)

Bash execution is NOT conditional based on Claude's reasoning - all `!` commands always run.

❌ **This doesn't work**:
```yaml
## Context
Status: !`git status`

## Task
If status shows changes, then run: !`git diff`  # Always runs!
```

✅ **Use this instead**:
```yaml
## Context
Status: !`git status`
All changes: !`git diff`  # Run both, Claude interprets

## Task
Analyze the above context and decide what to do.
```

#### Pattern 3: Complex Bash with Pipes/Logic

```yaml
---
allowed-tools: Bash(git:*), Bash(grep:*)
---

Modified Python files: !`git diff --name-only | grep '\.py$'`

Recent changes to config: !`git log --oneline --follow -- config.yaml | head -5`
```

You can use pipes, redirects, and bash logic within the backticks.

### Tool Permission Patterns

#### Wildcard Permissions

```yaml
# All git commands
allowed-tools: Bash(git:*)

# All docker commands
allowed-tools: Bash(docker:*)

# All npm commands
allowed-tools: Bash(npm:*)
```

#### Specific Command Permissions

```yaml
# Only specific git commands
allowed-tools: Bash(git status:*), Bash(git diff:*), Bash(git log:*)

# Only read-only docker commands
allowed-tools: Bash(docker ps:*), Bash(docker images:*)
```

#### Command with Subcommands

```yaml
# Docker compose (note the space)
allowed-tools: Bash(docker compose:*)

# Git with specific subcommands
allowed-tools: Bash(git branch:*), Bash(git log:*)
```

#### Multiple Tools

```yaml
# Git + testing + npm
allowed-tools: Bash(git:*), Bash(pytest:*), Bash(npm:*)

# File reading + git
allowed-tools: Read, Grep, Glob, Bash(git:*)
```

### Security Considerations

#### Principle of Least Privilege

❌ **Too Permissive**:
```yaml
allowed-tools: Bash(*)  # ANY bash command!
```

✅ **Appropriately Restricted**:
```yaml
allowed-tools: Bash(git:*), Bash(pytest:*)  # Only what's needed
```

#### Command Injection Risks

When using user arguments in bash commands:

❌ **Vulnerable**:
```yaml
!`git commit -m "$ARGUMENTS"`  # Can inject commands!
```

✅ **Safe** (using heredoc):
```yaml
git commit -m "$(cat <<'EOF'
$ARGUMENTS
EOF
)"
```

#### Read-Only vs Write Operations

**Read-only** (safe for automated use):
```yaml
allowed-tools: Bash(git status:*), Bash(git log:*), Bash(git diff:*)
```

**Write operations** (use cautiously):
```yaml
allowed-tools: Bash(git add:*), Bash(git commit:*), Bash(git push:*)
```

## File References

### Basic File Inclusion

```yaml
---
description: Review implementation
---

Review the implementation in @src/utils/helpers.js
```

When command runs, the file content is automatically included in context.

### Multiple File References

```yaml
Compare these implementations:

Old: @src/old-version.js
New: @src/new-version.js
Tests: @tests/test-version.js

Identify breaking changes.
```

### File Reference Patterns

```yaml
# Glob patterns supported
Review all utils: @src/utils/*.js

# Specific files
Check config: @config/database.json
Check env: @.env.example
```

## Extended Thinking (Progressive Budget Allocation)

Claude 3.7 Sonnet supports **progressive thinking budget levels** for complex reasoning tasks.

### Thinking Levels

Commands can trigger different levels of thinking by using specific keywords:

**Level 1: `think`** - Moderate complexity
- Budget: Standard
- Use for: Routine refactors, standard debugging
- Example: "Think about how to optimize this query"

**Level 2: `think hard`** - Challenging problems
- Budget: Increased
- Use for: Complex refactors, multi-file changes
- Example: "Think hard about architectural implications"

**Level 3: `think harder`** - Very complex issues
- Budget: High
- Use for: System design, complex algorithms
- Example: "Think harder about optimal data structure"

**Level 4: `ultrathink`** - Maximum reasoning
- Budget: Maximum
- Use for: Critical architectural decisions, security reviews
- Example: "Ultrathink about authentication strategy"

### Usage in Commands

```yaml
---
description: Refactor authentication system
model: opus
---

Think harder about this authentication refactoring: $ARGUMENTS

Consider:
1. Security implications
2. Backward compatibility
3. Performance impact
4. Migration strategy

Provide detailed analysis before implementing.
```

### When to Use Each Level

| Level | Use Case | Budget | Speed |
|-------|----------|--------|-------|
| `think` | Standard refactoring | ~5k tokens | Fast |
| `think hard` | Complex multi-file changes | ~15k tokens | Moderate |
| `think harder` | System design decisions | ~50k tokens | Slower |
| `ultrathink` | Critical architecture | ~100k+ tokens | Slowest |

### Best Practices

- Match thinking level to problem complexity
- Don't overuse `ultrathink` (expensive, slow)
- Combine with appropriate model (opus for complex)
- Use for upfront planning, not simple operations

### Trigger Keywords

Any variation works:
- "Think carefully", "Think through", "Consider deeply"
- "Think hard about", "Reason through"
- "Think harder", "Deeply analyze"
- "Ultrathink", "Maximum reasoning"

The model detects these phrases and allocates thinking budget accordingly.

## Testing Commands

### Test Method 1: Manual Invocation

```bash
# In Claude Code CLI
/your-command arg1 arg2
```

**Verify**:
- Command is recognized (appears in `/help`)
- Arguments substitute correctly
- Bash commands execute
- Expected output produced

### Test Method 2: Argument Substitution

```bash
/review-pr 123 high alice
```

**Check in output**:
- $1 replaced with "123"
- $2 replaced with "high"
- $3 replaced with "alice"

### Test Method 3: Bash Execution

Create test command:
```yaml
---
description: Test bash execution
allowed-tools: Bash(git:*)
---

Current branch: !`git branch --show-current`
Last commit: !`git log -1 --oneline`
```

**Verify**: Output includes actual branch name and commit.

### Test Method 4: Error Cases

Test with:
- Missing arguments
- Invalid arguments
- Commands that fail
- Permission issues

## Troubleshooting

### Issue 1: Command Not Found

**Symptoms**:
```
User: /mycommand
Claude: Unknown command
```

**Diagnose**:
```bash
# Check file exists
ls .claude/commands/mycommand.md

# Check filename matches command
# /mycommand needs mycommand.md (not my-command.md)

# Check file has content
cat .claude/commands/mycommand.md
```

**Fix**:
1. Ensure file exists at correct path
2. Filename matches command exactly
3. File has valid YAML frontmatter
4. Restart Claude Code

### Issue 2: Arguments Not Substituting

**Symptoms**:
```
User: /test arg1
Output: "Running test on $1"  # Literal $1!
```

**Diagnose**:
```yaml
# Check: Is $1 in prompt content (not frontmatter)?
---
description: Test with $1  # ❌ Won't substitute here
---

Test with $1  # ✅ Substitutes here
```

**Fix**:
- Use `$1`, `$2`, etc. in prompt content only
- Provide arguments when calling: `/command arg1`
- Don't escape `$`: not `\$1`

### Issue 3: Bash Commands Not Running

**Symptoms**:
```
Output includes: "!`git status`"  # Literal command!
```

**Diagnose**:
```yaml
# Missing allowed-tools?
---
# No allowed-tools specified
---

Branch: !`git branch`  # Won't run!
```

**Fix**:
```yaml
---
allowed-tools: Bash(git:*)
---

Branch: !`git branch --show-current`  # Now runs!
```

**Also check**:
- Has `!` prefix: `!`command``
- Has backticks: ` !`command` `
- Command is valid bash

### Issue 4: Permission Denied

**Symptoms**:
```
Error: Permission denied for tool: Bash(npm run test)
```

**Diagnose**:
```yaml
---
allowed-tools: Bash(git:*)  # Only git allowed
---

!`npm run test`  # ❌ npm not allowed!
```

**Fix**:
```yaml
---
allowed-tools: Bash(git:*), Bash(npm:*)  # Add npm
---

!`npm run test`  # ✅ Now works
```

### Issue 5: Command Runs But Output Missing

**Symptoms**:
Bash command executes but output doesn't appear in prompt.

**Diagnose**:
- Command might have no output
- Command might write to stderr
- Output might be empty

**Fix**:
```yaml
# Capture both stdout and stderr
!`git status 2>&1`

# Force output
!`git status || echo "No git repository"`
```

### Issue 6: Slow Command Execution

**Symptoms**:
Command takes long time to respond.

**Causes**:
- Too many bash commands
- Slow operations (network, large files)
- Complex bash logic

**Fix**:
1. Reduce number of bash commands
2. Use more efficient commands
3. Consider delegating to subagent for heavy work
4. Use haiku model for simple commands

## Best Practices Reference

### Heredoc for Multi-line Strings

Always use heredoc for commit messages or multi-line content:

```yaml
git commit -m "$(cat <<'EOF'
$ARGUMENTS

🤖 Generated with Claude Code
EOF
)"
```

**Why**: Prevents command injection, handles special characters safely.

### Context Before Instructions

```yaml
# Good
## Current State
!`git status`

## Task
Create commit based on above status

# Bad (no context)
Create a commit: $ARGUMENTS
```

### Tool Permission Documentation

```yaml
# Good - explains why
---
allowed-tools: Bash(git:*), Bash(pytest:*)
# git: for checking changes, pytest: for running tests
---

# Bad - no explanation
---
allowed-tools: Bash(*)
---
```

## Command Organization

### Flat Structure (Simple)

```
.claude/commands/
├── commit.md
├── test.md
├── deploy.md
└── review.md
```

All commands in root. Simple, works for < 10 commands.

### Subdirectory Structure (Organized)

```
.claude/commands/
├── git/
│   ├── commit.md
│   └── branch.md
└── testing/
    ├── run-tests.md
    └── coverage.md
```

**Note**: Subdirectories are for organization only. Command names stay the same:
- `/commit` works (not `/git/commit`)
- Help shows: "commit (project:git)"

## Validation Checklist

Before finalizing a command:

- [ ] Description is clear and concise
- [ ] Argument hints provided (if using args)
- [ ] Tool permissions specified (if using tools)
- [ ] Arguments use proper format ($ARGUMENTS or $1, $2, etc.)
- [ ] Bash commands have `!` and backticks
- [ ] Bash permissions match commands used
- [ ] Heredocs used for multi-line strings (commit messages)
- [ ] File tested with sample arguments
- [ ] No overly permissive tool access
- [ ] Command is thin wrapper if complex (delegates to skill)

## Quick Reference

### YAML Frontmatter Fields

```yaml
---
description: What the command does (optional)
argument-hint: [arg1] [arg2] (optional)
allowed-tools: Bash(git:*), Read (optional)
model: haiku|sonnet|opus (optional)
disable-model-invocation: false (optional)
---
```

### Argument Substitution

- `$ARGUMENTS` - All arguments as single string
- `$1`, `$2`, `$3`... - Individual positional arguments

### Bash Execution

- `!`command`` - Execute command, insert output
- Requires `allowed-tools: Bash(...)`
- Runs before prompt sent to Claude

### File References

- `@path/to/file` - Include file content in prompt
- Works with glob patterns
