---
title: Fix ES Futures 2000-tick chart different from Pats
slug: fix-es-tick-chart
date: 2025-08-28 00:00
status: published
tags: [trading]
description: 
---

It is normal for tick charts to look a little bit different between different charting software (NT8, QT, Sierra) and between different data providers (CQG, Denali), because of how each one aggregates and displays the tick data. The key here lies in **a little bit** different.

In a normal trading session, a "little bit different" means the swing highs and lows are identical between charts, the direction of the moves is identical, and most of the bars are identical, but here and there you might see a small difference in the open and closing prices of some bars (not all). Maybe Mack or Wade have a terrible signal bar where you have a great one, and vice-versa.

However, if there's a total mismatch, entire swings and legs missing, or you have 100 bars where Mack has two enormous ones, then that's a problem with the tick data aggregation. Even though we are all trading the same 2000-tick chart where each bar contains 2000 trades (regardless of volume traded), the charting software needs to decide at which point it resets counting at the start of a trading session and where is the cut-off point at the end of the session. Luckily, the fix is simple:

1. Make sure you only load as much data as needed. Mack loads 21 days of tick-data; I usually load 10 days - just enough for me to go back and review last week's trades.
2. Check your **session trading times**!!! This is the actual fix.
3. Specific for NT users, make a habit of [reloading your chart data](https://support.ninjatrader.com/s/article/How-Do-I-Reload-Historical-Data-on-NinjaTrader-Desktop?language=en_US) _every single day_.

If you have RTH/ETH session times, or "after hours", or "day session" and " evening session", turn it all off. Set your open to 17h CST, your close to 16h CST. Those are the actual opening/closing times for CME Equity Futures and should be the starting and ending times for aggregating tick data[^times]. If you want to highlight a time period in your chart, like Mack does, use an indicator or study.

[^times]: When trading the S&P 500 emini or any other index futures, we talk about the "open" at 8:30 CST (Chicago timezone), about the "pre-market", Mack even talks about the "close" at 15:00 CST. This only applies to the **underlying** index components we are tracking, as NYSE and Nasdaq exchanges do have RTH (regular trading hours) and ETH (extended trading hours). Many big players trading the underlying stocks are bound to those trading hours, which is why we see more volume around the "open". However, the [S&P 500 futures](https://www.cmegroup.com/markets/equities/sp/e-mini-sandp500.contractSpecs.html) DO NOT have RTH and ETH times. They open on Sunday at 17:00, they close on Friday at 16:00, trade 23h a day, end of the story.
