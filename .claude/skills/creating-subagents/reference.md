# Subagent Reference Documentation

This file contains detailed reference information about tool restrictions, model selection, and advanced configuration options.

## Tool Restrictions

Use the `tools` field to limit what the subagent can do, following the principle of least privilege.

### Read-Only Subagent

```yaml
tools: Read, Grep, Glob
```

**Good for**:
- Code reviewers
- Analyzers
- Documentation readers
- Security auditors
- Report generators

**Why Read-Only**:
- Prevents accidental modifications
- Safe for automated workflows
- Can run on production code without risk
- Faster (no file write operations)

**Example Use Case**:
```yaml
---
name: code-analyzer
description: Analyzes code complexity and suggests improvements. Read-only.
tools: Read, Grep, Glob
---
```

### Limited Write Subagent

```yaml
tools: Read, Write, Edit, Grep, Glob
```

**Good for**:
- Code generators
- File refactorers
- Test writers
- Documentation generators
- Code formatters

**Why Limited Write**:
- Can create/modify files but not execute commands
- Safe for code generation tasks
- No system access reduces security risks
- Sufficient for most code manipulation

**Example Use Case**:
```yaml
---
name: test-generator
description: Generates unit tests based on existing code.
tools: Read, Write, Edit, Grep, Glob
---
```

### Full Access Subagent

```yaml
# Omit tools field to inherit all tools from main conversation
```

**Good for**:
- Deployment agents
- Complex workflow orchestrators
- System administrators
- Build and CI/CD managers

**Why Full Access**:
- Needs to run system commands
- Requires flexible tool access
- Trusted for production operations

**Example Use Case**:
```yaml
---
name: deployment-manager
description: Handles deployment workflows with full system access.
# No tools field = inherits all tools
---
```

### Critical: MCP Tool Inheritance

**IMPORTANT SECURITY NOTE**: When you omit the `tools` field, the subagent inherits **ALL tools** from the main conversation, including **all MCP server tools**.

**Example:**
```yaml
---
name: my-subagent
# No tools field specified
---
```

This subagent now has access to:
- All standard tools (Read, Write, Edit, Bash, etc.)
- All MCP server tools currently connected
- Any custom tools available in the session

**Security Implications:**
- Read-only subagents should explicitly specify: `tools: Read, Grep, Glob`
- Review subagents should NOT have MCP tools that modify state
- Use `/agents` command to see full list of available MCP tools

**Best Practice**: Always explicitly specify the `tools` field for production subagents following principle of least privilege.

### Selective Bash Access (Recommended for Security)

```yaml
tools: Read, Bash(git:*), Bash(docker:*), Bash(pytest:*)
```

**Good for**:
- Git workflow automation
- Containerized operations
- Test runners
- Any task needing specific command access

**Why Selective**:
- Security: Only allows specific commands
- Prevents accidental system modifications
- Documents which commands are needed
- Easier to audit and review

**Pattern Examples**:
```yaml
# Git operations only
tools: Read, Bash(git:*)

# Testing only
tools: Read, Bash(pytest:*), Bash(npm test:*)

# Docker operations
tools: Read, Bash(docker:*), Bash(docker compose:*)

# Multiple specific commands
tools: Read, Write, Bash(git:*), Bash(npm:*), Bash(pytest:*)
```

## Model Selection

Choose the right model based on task complexity, speed requirements, and cost considerations.

### Sonnet (Default, Balanced)

```yaml
model: sonnet
```

**Characteristics**:
- **Speed**: Medium-fast
- **Quality**: High
- **Cost**: Medium
- **Reasoning**: Strong

**Best for**:
- Most standard tasks
- Code review
- Test generation
- Refactoring
- Documentation
- Default choice when unsure

**Not Ideal for**:
- Simple pattern matching (use haiku)
- Critical architecture decisions (use opus)

### Haiku (Fast, Economical)

```yaml
model: haiku
```

**Characteristics**:
- **Speed**: Very fast
- **Quality**: Good for simple tasks
- **Cost**: Low (cheapest)
- **Reasoning**: Basic

**Best for**:
- File search and pattern matching
- Simple code formatting
- Log parsing
- Quick analysis
- High-volume operations
- Cost-sensitive workflows

**Example Use Cases**:
```yaml
---
name: file-finder
description: Quickly locates files matching patterns
tools: Read, Grep, Glob
model: haiku  # Fast, cheap, sufficient for search
---
```

**Not Ideal for**:
- Complex reasoning
- Architecture decisions
- Security-critical reviews

### Opus (Powerful, Thoughtful)

```yaml
model: opus
```

**Characteristics**:
- **Speed**: Slower
- **Quality**: Highest
- **Cost**: High (most expensive)
- **Reasoning**: Most sophisticated

**Best for**:
- Complex architectural decisions
- Security reviews
- Critical refactorings
- Algorithm optimization
- Design pattern selection
- When accuracy is critical

**Example Use Cases**:
```yaml
---
name: security-auditor
description: Deep security analysis of authentication systems
tools: Read, Grep, Glob
model: opus  # Critical task, needs best reasoning
---
```

**Trade-offs**:
- Slower than sonnet/haiku
- More expensive
- Best quality and reasoning

### Inherit (Match Main Conversation)

```yaml
model: inherit
```

**Characteristics**:
- Uses whatever model the main conversation is using
- Ensures consistent capabilities
- No model switching overhead

**Best for**:
- Tightly integrated subagents
- When consistency with main thread matters
- Subagents that continue main conversation's work

**Example Use Cases**:
```yaml
---
name: code-completer
description: Completes code started in main conversation
model: inherit  # Maintain same quality/style
---
```

**When NOT to use**:
- When subagent needs different capabilities
- When cost optimization is important
- When speed matters more than consistency

## Model Selection Decision Tree

```
Is the task simple and pattern-based?
├─ Yes → Haiku (fast, cheap)
└─ No → Is it mission-critical or security-related?
    ├─ Yes → Opus (best reasoning)
    └─ No → Sonnet (default balanced choice)
```

## Tool and Model Combination Examples

### Example 1: Fast File Analyzer
```yaml
---
name: file-analyzer
description: Quick analysis of file structure
tools: Read, Grep, Glob
model: haiku  # Fast + read-only = perfect combination
---
```

### Example 2: Security Code Reviewer
```yaml
---
name: security-reviewer
description: Deep security analysis
tools: Read, Grep, Glob  # Read-only for safety
model: opus  # Best reasoning for critical task
---
```

### Example 3: Standard Code Generator
```yaml
---
name: code-generator
description: Generate boilerplate code
tools: Read, Write, Edit, Grep, Glob
model: sonnet  # Balanced for code generation
---
```

### Example 4: Deployment Orchestrator
```yaml
---
name: deployment-manager
description: Production deployment with checks
tools: Read, Bash(git:*), Bash(docker:*), Bash(kubectl:*)
model: sonnet  # Good reasoning + specific commands
---
```

## Common Pitfalls

### Too Restrictive
❌ **Bad**:
```yaml
tools: Read  # Can't do anything useful
```

✅ **Good**:
```yaml
tools: Read, Grep, Glob  # Can analyze code
```

### Too Permissive
❌ **Bad**:
```yaml
tools: *  # Everything! Dangerous and unclear
```

✅ **Good**:
```yaml
tools: Read, Write, Bash(git:*), Bash(npm:*)  # Specific needs
```

### Wrong Model Choice
❌ **Bad**:
```yaml
name: simple-formatter
model: opus  # Overkill for simple task
```

✅ **Good**:
```yaml
name: simple-formatter
model: haiku  # Fast and sufficient
```

## Security Best Practices

1. **Start Restrictive**: Begin with minimal tools, add as needed
2. **Document Why**: Comment why each tool is needed
3. **Use Bash Patterns**: Prefer `Bash(git:*)` over full bash access
4. **Audit Regularly**: Review tool permissions periodically
5. **Read-Only When Possible**: Use read-only for analysis tasks

## Resumable Subagents (Continuation Pattern)

Subagents can be resumed across multiple invocations using `agentId` to maintain full conversation context.

### How It Works

Each subagent execution gets a unique `agentId`:
- Conversation stored in `agent-{agentId}.jsonl` files
- Can resume the same subagent later with full context
- Useful for long-running tasks broken into sessions

### When to Use

- **Long-running research** that needs multiple sessions
- **Iterative analysis** building on previous work
- **Complex investigations** that can't complete in one go
- **Stateful workflows** requiring memory across invocations

### Example Workflow

```
Session 1:
User: "Use code-analyzer subagent to analyze auth system"
Claude: [Subagent analyzes, gets agentId: abc123]
Output: "Analysis started, need more time..."

Session 2:
User: "Resume code-analyzer agent abc123 to continue"
Claude: [Resumes with full context from previous session]
Output: "Continuing analysis... [complete findings]"
```

### Implementation

Subagents automatically get agentId on creation. To resume:
1. Note the agentId from first execution
2. Request resumption: "Resume {agent-name} agent {agentId}"
3. Subagent continues with full previous context

### Storage

- Stored in: `.claude/agent-logs/agent-{agentId}.jsonl`
- Contains full conversation history
- Persists across Claude Code restarts
