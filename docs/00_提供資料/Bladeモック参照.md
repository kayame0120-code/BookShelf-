# Bladeモック参照

このファイルはBladeモック本体を置かない。参照情報のみを1枚にまとめたもの。
モック本体はGitHubリポジトリ側にある。

## リポジトリ

```
https://github.com/coachtech-prepared-file/Preparedblade-mockcase-BookShelf.git
```

## ブランチ

| ブランチ | 用途 | 状態 |
|---|---|---|
| `basic` | 基本機能の正本 | frozen（改変禁止） |
| `advanced` | 応用機能の正本 | frozen（改変禁止） |

## frozen宣言

両ブランチとも改変禁止。要件シート・機能仕様書・発注書の記述とBladeモックが食い違った場合、**Bladeモックが優先する**（`CLAUDE.md` 0章の正本優先順位1位）。

## Cloneコマンド

```bash
# 基本機能（Week 1〜4で参照）
git clone --depth 1 -b basic https://github.com/coachtech-prepared-file/Preparedblade-mockcase-BookShelf.git blade-basic

# 応用機能（Week 5〜6で参照）
git clone --depth 1 -b advanced https://github.com/coachtech-prepared-file/Preparedblade-mockcase-BookShelf.git blade-advanced
```

## advancedブランチの扱い

- 移入タイミング・`migrate:fresh --seed` による再構築手順は第5週に確定し、このセクションへ追記する。
- 現時点（第2週）ではbasicブランチのみを参照対象とする。
