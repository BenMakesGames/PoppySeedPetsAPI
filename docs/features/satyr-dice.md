# Satyr Dice

## Overview

Satyr Dice is a dice-rolling mini-game hosted by the NPC **Kat** at the **Florist** shop. Players spend **recycling points** to roll two magical six-sided dice, earning rewards based on the total. A betting mechanic and a rare day-of-week coin collection system add strategic depth and long-term engagement.

The feature serves two primary purposes in the game economy:

1. **Recycling point sink.** Recycling points are a major currency; Satyr Dice gives players a compelling reason to spend them in volume.
2. **Primary source of Baabbles.** Baabbles (loot containers with tiered rarity) flow primarily from this game, making it the main entry point into the Baabble item ecosystem.

---

## Access and Prerequisites

- **Unlock condition:** The Florist is unlocked when a player **follows another player** (via `FollowController`).
- **Location:** Accessible at `/florist/satyrDice` from the Florist shop page.
- **Cost:** 100 recycling points per roll.
- **Roll limit:** None. Players may roll as many times as they can afford. This is intentional — the feature is designed as a currency sink.

> **Design note:** The Florist unlock is tied to a social action because the shop's original feature (Flowerbombs) is inherently social. Satyr Dice was added later to make the Florist more engaging and was gated here as a matter of progression pacing rather than thematic fit. The connection between "follow a player" and "roll magic dice" is acknowledged as a quirk, but in practice players discover the dice naturally while exploring the Florist and rarely question the pairing.

---

## Core Mechanics

### The Dice

Each die is a d6 (values 1-6) with a hidden **7th face** representing the current day of the week. When a die lands on its 7th face, it counts as **0** toward the total and awards a **day-of-week coin**.

**Day-of-week probability:**
- First die: 1/20 chance (5%) of showing the day-of-week face.
- Second die: 1/(20 - first die's value) chance. This means the second die's day-of-week probability is *coupled* to the first die's result — a higher first-die value increases the second die's chance of hitting the 7th face. See [Known Quirks](#known-quirks) for discussion.

If a die shows the day-of-week face, the player receives the coin for the current real-world day (e.g., rolling on a Wednesday awards a Wednesday Coin).

### Reward Table

| Total | Reward |
|-------|--------|
| 12 | 2x Shiny Baabble |
| 11 | 1x Shiny Baabble |
| 10 | 1x Gold Baabble |
| 9 | 1x White Baabble |
| 8 | 1x Black Baabble + 75 recycling points |
| 5-7 | 1x Black Baabble |
| 3-4 | Creamy Milk + Paper Bag |
| 2 | Creamy Milk |
| 1 | Quintessence |
| 0 | 10x Quintessence |

Day-of-week coins are awarded *in addition to* the table reward whenever a die shows its 7th face.

### The Bet

Before rolling, the player must predict the outcome:

| Bet | Wins if... | Approximate win rate (normal 2d6) |
|-----|-----------|-----------------------------------|
| "Higher than 8!" | Total > 8 | ~28% |
| "Exactly 8!" | Total = 8 | ~14% |
| "Lower than 8!" | Total < 8 | ~58% |

A correct bet **doubles all winnings**: items are duplicated (via `array_merge($items, $items)`) and recycling point rewards are doubled.

**Design intent:** The pivot point of 8 is deliberately above the 2d6 median of 7. This creates a risk/reward tradeoff:
- **"Lower"** is the safe bet (~58%) but the rewards below 8 are mostly low-value (Creamy Milk, Paper Bag, Quintessence).
- **"Higher"** is risky (~28%) but doubles the valuable Baabble rewards.
- **"Exactly 8"** is improbable (~14%) but uniquely rewarding: the only outcome that returns recycling points (75 base, 150 doubled), meaning a correct call yields a net profit of +50 points plus two Black Baabbles.

In practice, most players default to "Higher than 8." The day-of-week coin mechanic (which makes the total lower) was designed in part to give players a reason to bet "Lower" — since a day-of-week roll pulls the total down, betting low becomes strategically interesting when chasing coins.

### Doubling and Coins

The doubling mechanic applies to the *entire* items array, including day-of-week coins. This is intentional: if a player rolls a day-of-week face and wins their bet, they receive two copies of that day's coin. This accelerates Gift Package collection and rewards players who bet strategically around the coin mechanic.

---

## Day-of-Week Coins and Kat's Gift Package

### Coin Collection

There are 7 coins, one for each day of the week:

| Coin | Day | Stat Bonus (as tool) |
|------|-----|---------------------|
| Monday Coin | Monday | +1 Stealth |
| Tuesday Coin | Tuesday | +1 Brawl |
| Wednesday Coin | Wednesday | +1 Arcana |
| Thursday Coin | Thursday | +1 Nature |
| Friday Coin | Friday | +1 Sex Drive |
| Saturday Coin | Saturday | +1 Gathering |
| Sunday Coin | Sunday | +1 Resource Gathering |

Coins can only be obtained from Satyr Dice. A player must play on at least 7 different days of the week and get lucky enough to roll the 7th face each time to collect a full set.

### Kat's Gift Package

Trading a complete set of all 7 coins to Kat awards **Kat's Gift Package** (`POST /florist/tradeForGiftPackage`). The trade button only appears after the player has rolled Satyr Dice at least once.

Opening the Gift Package presents 4 choices (player picks one):

| Option | Contents |
|--------|----------|
| Baabbles | 14 Baabbles (6 Black, 5 White, 4 Gold, 3 Shiny) + 1 Lotus Flower |
| Magma | 67 Liquid-hot Magma + 1 Lotus Flower |
| Illusions | 1 Scroll of Illusions + 1 Lotus Flower |
| Serum | 1/2 Species Transmigration Serum + 1 Lotus Flower |

All Baabbles from the Gift Package receive the "Bleating" spice. A Lotus Flower is included with every option.

---

## Baabbles

Baabbles are loot containers with four tiers of increasing quality: **Black < White < Gold < Shiny**. Higher tiers contain more items and a higher proportion of rare drops.

For full loot table details, see `api/src/Controller/Item/Pinata/BaabbleController.php`. At a high level:

| Tier | Lame | Okay | Good | Weird | Rare |
|------|------|------|------|-------|------|
| Black | 2-8 | 7-17 | 0-9 | 1 | 0 |
| White | 4-14 | 10-18 | 0-9 | 1 | 1 |
| Gold | 4-14 | 6-16 | 0-12 | 0-10 | 1-5 |
| Shiny | 0-12 | 4-16 | 4-16 | 4-14 | 3-7 |

### Baabble Sources Outside Satyr Dice

Satyr Dice is the **primary source** of Baabbles, but not the only one:

- **Kat's Gift Package** (Baabbles option): 14 mixed Baabbles — but this itself requires Satyr Dice to obtain the coins.
- **Night and Day item**: 1 Black + 1 White Baabble (33% chance, one of three possible pairs).
- **Monster of the Week (Vaf & Nir)**: 1 Gold Baabble as a medium-difficulty reward.
- **Trader NPC**: Convert 1 Black Baabble + 25 recycling points + 25 money into 1 White Baabble (upgrade path, not a new source).
- **1000 Baabbles Opened badge**: Awards 1 Shiny Baabble.

These alternate sources are intentionally minor. Satyr Dice should feel like the meaningful way to acquire Baabbles.

---

## Badges and Stats

### Stats Tracked

| Stat | Trigger |
|------|---------|
| `Rolled Satyr Dice` | Incremented each roll |
| `Traded for Kat's Gift Package` | Incremented each coin-set trade |
| `Kat's Gift Packages Opened` | Incremented when opening a Gift Package |
| `Opened a [Tier] Baabble` | Incremented per Baabble opened (per tier) |

### Related Badges

| Badge | Condition | Reward |
|-------|-----------|--------|
| Goodies! | Open 1 Baabble | Key Ring |
| MOAR GOODIES! | Open 10 Baabbles | Carrot Key |
| *bleats* | Open 100 Baabbles | Winged Key |
| Patron of the Cosmic Goat | Open 1,000 Baabbles | Skill Scroll: Crafts + 1 Shiny Baabble |
| Weekday Coins Traded 1 | Trade 1 Gift Package | 7x Quintessence |
| Weekday Coins Traded 7 | Trade 7 Gift Packages | 7x Quintessence |

---

## UI and Presentation

### Flow

1. **Welcome** — Kat offers to let the player roll for 100 recycling points.
2. **Rules** (optional) — Player can ask about the rules, Baabbles, or day-of-week coins via dialog choices. Each is a separate explanatory screen.
3. **Play** — Two animated `SpinningD6` components display. Player selects their bet ("Higher!", "Exactly 8!", "Lower!"). Buttons are disabled while the roll is in flight.
4. **Results** — Dice settle to their final values (day-of-week name if applicable). Kat announces the result with contextual dialogue. Player can roll again or return to the Florist.

### Sound

Three dice-roll sound variants (`roll-die-a`, `roll-die-b`, `roll-die-c`) — one is selected randomly per roll.

### Results Dialogue

Kat's commentary varies based on the total and bet outcome:
- Totals >= 11 or <= 3: "Oh, dang! {total}!" (notable rolls)
- Correct bet: "And you nailed it!" / "Nice! You called it!"
- Incorrect bet: "Less/More than an 8... but hey, you still get {rewards}."

---

## Known Quirks

### Second Die Day-of-Week Probability Coupling

The second die's chance of landing on the day-of-week face is `1/(20 - r1)` where `r1` is the first die's numeric result (or 0 if the first die hit day-of-week). This means:

| First die result | Second die day-of-week chance |
|-----------------|-------------------------------|
| 0 (day-of-week) | 1/20 (5.0%) |
| 1 | 1/19 (5.3%) |
| 2 | 1/18 (5.6%) |
| 3 | 1/17 (5.9%) |
| 4 | 1/16 (6.3%) |
| 5 | 1/15 (6.7%) |
| 6 | 1/14 (7.1%) |

The original intent of this coupling is unclear. The practical effect is that higher first-die rolls slightly increase the chance of a second coin — which is somewhat counterintuitive if coins are meant as a consolation for low totals. The impact on overall game balance is minor but worth being aware of.

### Naming: RecyclingGame

The frontend component is `RecyclingGameComponent`, the route is `/florist/satyrDice`, and the data model is `RecyclingResultModel`. The "recycling game" naming reflects the feature's origin as a recycling-point spending mechanism. Whether to align these names is a low-priority housekeeping decision.

---

## Gaps and Future Opportunities

### Double-Zero Ceremony

Rolling 0 (both dice hit day-of-week) is approximately a 1-in-400 event. Currently it receives the same UI treatment as any other roll — Kat says "Oh, dang! 0!" and the rewards are listed. This is a missed opportunity for a memorable moment. Consider: special animation, unique Kat dialogue, a sound effect, a flash message, or an activity log entry that commemorates the event.

### Discoverability

The only path to Satyr Dice is through the Florist, which is unlocked by following another player. There is no tutorial, tooltip, or help entry pointing players toward it. This is consistent with the game's core design philosophy — discovery-by-exploration is an explicit value communicated to players on first sign-in. Satyr Dice is meant to be found, not signposted.

### No "Dry Run" or Preview

Players must commit 100 recycling points before seeing the dice. There's no way to observe the game without paying. The rules screens mitigate this, but a free first roll or a spectator mode could lower the barrier to engagement.

---

## Implementation Reference

| Concern | File |
|---------|------|
| Roll logic, payout table, betting | `api/src/Controller/Florist/RollSatyrDiceController.php` |
| Gift Package trade | `api/src/Controller/Florist/TradeForKatsGiftPackageController.php` |
| Gift Package opening | `api/src/Controller/Item/Pinata/KatsGiftController.php` |
| Baabble loot tables | `api/src/Controller/Item/Pinata/BaabbleController.php` |
| Florist shop / eligibility check | `api/src/Controller/Florist/GetShopInventoryController.php` |
| Frontend game UI | `webapp/src/app/module/florist/page/recycling-game/` |
| Spinning die component | `webapp/src/app/module/florist/component/spinning-d6/` |
| Routing | `webapp/src/app/module/florist/florist-routing.module.ts` |
| Day-of-week coin items | `api/migrations/2024/07/Version20240705020421.php` |
| Badge definitions | `api/src/Controller/Achievement/BadgeHelpers.php` |
| Recycling point economy | `api/src/Service/RecyclingService.php`, `api/src/Service/TransactionService.php` |
