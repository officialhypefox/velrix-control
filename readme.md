# Velrix Control: a modified version of Pelican Panel

**This is a modified fork of [Pelican Panel](https://github.com/pelican/panel).** It is maintained by
Hypefox AB and runs as the control panel behind [Velrix](https://www.velrix.net), which hosts Discord
bots and applications. It is not affiliated with or endorsed by the Pelican project.

Pelican Panel is licensed under the GNU Affero General Public License v3. Section 5(a) of that licence
requires a modified version to state that it was changed and when, and section 13 requires that anyone
using it over a network can obtain the source. This notice covers the first, and the second is covered by
this repository being public: what you see here is the source of the panel we actually run.

* Upstream project: https://github.com/pelican/panel
* This fork: https://github.com/officialhypefox/velrix-control
* Licence: AGPL-3.0, unchanged. See [`license`](license).

First modified 3 September 2025. Most recent modification 2 August 2026.

Upstream is merged in regularly, so this fork tracks Pelican rather than diverging from it. Everything
listed below is a change we made on top.

## What we changed

### Single sign-on from the Velrix dashboard (25 July 2026)

Velrix creates panel accounts for its users, and those users never set a panel password, so there is no
way for them to sign in the normal way. A new endpoint accepts a short-lived token signed by the Velrix
backend (HMAC-SHA256 over a base64url JSON payload, using a secret shared between the two), verifies it
in constant time, opens a normal panel session and lands the user on their server.

* Added `app/Http/Controllers/Auth/VelrixSsoController.php`
* Added the `/auth/sso` route in `routes/auth.php`
* Added `config/velrix.php`, which holds the shared secret. Single sign-on refuses every request while
  that secret is empty, so an unconfigured install cannot be signed into this way.

### Higher API rate limits (25 July 2026)

Pelican keys its client and application API limiters by the requesting user, which assumes one human
clicking around. Velrix brokers every one of its users' server actions through a single key, so those
buckets are the whole platform's shared throughput and the stock defaults throttled almost immediately.
The affected limits in `config/http.php` were raised well above stock. Velrix enforces its own per-user
limits before a request ever reaches the panel. Every value is still overridable by its environment
variable, so an install that does not front the panel this way can put them back.

### An explanation on locked account fields (2 August 2026)

Upstream already locks username, email and password for externally managed users, which is correct, but
it locks them silently. A greyed-out box with no reason reads as a broken panel and the user's only next
move is a support ticket. The fields now say who owns them and where to change them, and the equivalent
API responses return that sentence instead of a bare "This action is unauthorized".

* `app/Filament/Pages/Auth/EditProfile.php`
* `app/Http/Requests/Api/Client/Account/UpdateEmailRequest.php`,
  `UpdatePasswordRequest.php` and `UpdateUsernameRequest.php`
* `lang/en/profile.php`

No permission changed here. The fields were locked before and are locked now.

### Branding (20 September 2025, and 13 February 2026)

Pelican's logo and favicon in `public/` were replaced with ours, and the footer credits Hypefox AB
alongside Pelican rather than replacing the Pelican credit.

### Build and deploy workflows (from 9 November 2025)

Upstream's release, Docker publish, Crowdin and CLA workflows were removed, and one deploy workflow was
added for our own infrastructure. Nothing here affects the panel at runtime, and none of it would be
useful to anyone not deploying to our servers.

## Seeing the changes for yourself

This list is written by hand, so the repository is the authority, not this file. To see every line we
changed against upstream:

```bash
git clone https://github.com/officialhypefox/velrix-control.git
cd velrix-control
git remote add upstream https://github.com/pelican/panel.git
git fetch upstream
git diff upstream/main...HEAD
```

---

Everything below this line is Pelican's own readme, kept as it is upstream.

<img width="20%" src="https://raw.githubusercontent.com/pelican/panel/main/public/pelican.svg" alt="logo">

# Pelican Panel

**Fly High, Game On: Pelican's pledge for unrivaled game servers.**

![Total Downloads](https://img.shields.io/github/downloads/pelican/panel/total?style=flat&label=Total%20Downloads&labelColor=rgba(0%2C%2070%2C%20114%2C%201)&color=rgba(255%2C%20255%2C%20255%2C%201)) 
![Latest Release](https://img.shields.io/github/v/release/pelican/panel?style=flat&label=Latest%20Release&labelColor=rgba(0%2C%2070%2C%20114%2C%201)&color=rgba(255%2C%20255%2C%20255%2C%201))  

Pelican Panel is a free, open-source game server control panel built for communities, hosts, and self-hosters.
It gives users a modern web UI for creating and managing game servers while running each server in an isolated Docker container through Wings.

## Why Pelican?

Use Pelican if you want:
- A modern alternative in the Pterodactyl ecosystem
- Docker-isolated game servers
- Support for Minecraft, SteamCMD games, databases, bots, voice servers, and more
- A free, open-source panel suitable for personal servers, communities, and hosting providers

## Support

* [Read the documentation](https://pelican.dev/docs)
* [Join the Discord](https://discord.gg/pelican-panel)
* [Wings](https://github.com/pelican/wings)
* [Open a GitHub Discussion for general project questions](https://github.com/pelican/panel/discussions)
* [Open an Issue for confirmed bugs](https://github.com/pelican/panel/issues)

## Supported Games and Servers

Pelican supports a wide variety of games by utilizing Docker containers to isolate each instance.
This gives you the power to run game servers without bloating machines with a host of additional dependencies.

Some of our popular eggs include:

| Category                                                             | Eggs            |               |                    |                |
|----------------------------------------------------------------------|-----------------|---------------|--------------------|----------------|
| [Minecraft](https://github.com/pelican-eggs/minecraft)               | Paper           | Sponge        | Bungeecord         | Waterfall      |
| [SteamCMD](https://github.com/pelican-eggs/steamcmd)                 | 7 Days to Die   | ARK: Survival | Arma 3             | Counter Strike |
|                                                                      | DayZ            | Enshrouded    | Left 4 Dead        | Palworld       |
|                                                                      | Project Zomboid | Satisfactory  | Sons of the Forest | Starbound      |
| [Standalone Games](https://github.com/pelican-eggs/games-standalone) | Among Us        | Factorio      | FTL                | GTA            |
|                                                                      | Kerbal Space    | Mindustry     | Rimworld           | Terraria       |
| [Discord Bots](https://github.com/pelican-eggs/chatbots)             | Redbot          | JMusicBot     | Dynamica           |                |
| [Voice Servers](https://github.com/pelican-eggs/voice)               | Mumble          | Teamspeak     | Lavalink           |                |
| [Software](https://github.com/pelican-eggs/software)                 | Elasticsearch   | Gitea         | Grafana            | RabbitMQ       |
| [Programming](https://github.com/pelican-eggs/generic)               | Node.js         | Python        | Java               | C#             |
| [Databases](https://github.com/pelican-eggs/database)                | Redis           | MariaDB       | PostgreSQL         | MongoDB        |
| [Storage](https://github.com/pelican-eggs/storage)                   | S3              | SFTP Share    |                    |                |
| [Monitoring](https://github.com/pelican-eggs/monitoring)             | Prometheus      | Loki          |                    |                |

## Contributing

We welcome contributions from developers, designers, translators, testers, documentation writers, and egg maintainers.

Good places to start:

- Read `contributing.md`
- Browse open issues
- Join Discord and ask where help is needed
- Improve docs or submit egg updates

## Supporting the Project

Pelican is built and maintained by volunteers. If Pelican helps you or your community, consider supporting ongoing development:

- [Sponsor the project](https://hub.pelican.dev/sponsor)
- [Contribute code or documentation](https://github.com/pelican/panel)
- [Help answer questions in Discord](https://discord.com/channels/1218730176297439332/1219038617133912084)
- Share Pelican with other server owners

## Repository Activity
![Stats](https://repobeats.axiom.co/api/embed/4d8cc7012b325141e6fae9c34a22b3669ad5753b.svg "Repobeats analytics image")

*Copyright Pelican® 2024-2026*
