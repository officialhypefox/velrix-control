# Velrix Control — working notes

A **fork of the Pelican panel** (`pelican/panel`, PHP/Laravel). It is the server control
plane Velrix drives through the panel's APIs; it is not a Velrix-authored codebase.

Pelican is a game-server panel upstream, but Velrix does not offer game servers yet. What it
provisions today is Discord bots and apps: the egg catalogue is Python, Node.js, Bun, Go, Java
and Rust runtimes plus Red-DiscordBot and Modmail. Worth keeping straight when writing copy or
naming things, since the upstream vocabulary implies a product we do not ship.

## Treat it as a fork first

Upstream is `https://github.com/pelican/panel`. Nearly every file here is theirs, and it is
merged in periodically ("Merge upstream Pelican into the fork").

The org renamed from `pelican-dev` to `pelican`, so any reference to the old name is stale.
The local `upstream` remote still holds the old URL and works only because GitHub 301s it;
`git remote set-url upstream https://github.com/pelican/panel.git` when convenient.

- **Keep the diff against upstream as small as it can be.** Every line changed here is a line
  to reconcile at the next merge. If a behaviour can live in the Velrix API instead, put it
  there.
- Before changing a file, check whether it is upstream's. Reformatting or "tidying" upstream
  code creates merge conflicts that carry no benefit.
- The Velrix API talks to this panel over its application and client APIs. Prefer adding a
  call on our side to patching a controller here.

## Reading the panel's own code is usually the answer

Its API responses are not shaped the way they look. The serializer writes `relationships`
**into** the item data, which `item()` then wraps as `attributes` — so an include arrives at
`res.relationships.<name>.data`, not `res.<name>`. And the same field can differ between the
two APIs: `EggVariable.rules` is cast to an array on the application API and joined with `|`
on the client API; `user_editable` and `is_editable` are both spellings you will meet.

Both of those cost real production bugs on the Velrix side, found only by opening
`PanelSerializer.php` and the resource classes. When a response does not look how you expect,
read the panel source rather than inferring from a sample.

## Deployment

Needs `PELICAN_CLIENT_TOKEN` and `VELRIX_SSO_SECRET` configured for the in-app console, power
controls, file browser and panel SSO to work.

The production node runs real user containers. Never run sweeping `docker` commands against
it, and never use `--remove-orphans` on the Velrix compose stack.
