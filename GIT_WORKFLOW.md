# Git Workflow

This repository is now connected to `origin` on `main`.

Use this pattern for future updates:

1. Make code changes in the workspace.
2. Check the diff with `git status --short`.
3. Stage only the intended files with `git add -A` or specific paths.
4. Create a focused commit:
   - `git commit -m "Short descriptive message"`
5. Push the branch:
   - `git push`

Notes:

- Do not run `git init` again in this directory.
- Do not recreate the repository metadata unless the `.git` directory is truly damaged.
- Keep commits small and descriptive so history stays readable.
- If the remote ever changes, verify it with `git remote -v` before pushing.
