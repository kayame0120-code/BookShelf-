# ReadingPlanPolicy 完了済み計画の編集不可（要件シート シート10 R29）検品

## やったこと（事実のみ）
- コミット 03bbdbf の差分範囲を `git show --stat` / `git show -- <file>` で取得した。
- ReadingPlanPolicy 全文と ReadingPlanController の edit/update の authorize 呼び出しを Read/grep で取得した。
- 追加2テスト・ReadingPlanTest 全体・全体テスト・対象2ファイルの Pint を実行し生出力を取得した。

## 変更ファイル

`git show --stat 03bbdbf` 生出力：
```
commit 03bbdbfd881705c5f30833963840ba1718e6ebdb
Author: ayame katakura <k.ayame0120@gmail.com>
Date:   Sun Sep 27 11:04:14 2026 +0900

    fix: 完了済み読書計画の編集をサーバ側で拒否(ReadingPlanPolicy::update)

    要件シート シート10 R29「完了済み計画は編集不可」に対し、従来 update
    ポリシーは所有者チェックのみでURL直打ちのPUT/GET editを防げなかった。
    update() に status !== Completed の条件を追加し、edit(GET)・update(PUT)
    の両方が403になるようにする（両アクションとも authorize('update') 経由）。

    - app/Policies/ReadingPlanPolicy.php: update() に1条件追加
      （complete()/delete()/所有者チェックは変更なし）
    - tests/Feature/ReadingPlanTest.php: 本人でも完了済み計画の
      GET edit・PUT update が403になる実HTTPテストを2本追加
      （PUT側は値が変わらないことも確認）

    Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>

 app/Policies/ReadingPlanPolicy.php |  3 ++-
 tests/Feature/ReadingPlanTest.php  | 26 ++++++++++++++++++++++++++
 2 files changed, 28 insertions(+), 1 deletion(-)
```

## 証拠（生の実行結果のみ）

### 1. 実装差分の範囲（update() の1条件追加のみ／complete()・delete() 不含）

実行コマンド：
```
$ git show 03bbdbf -- app/Policies/ReadingPlanPolicy.php
```

出力（差分本体のみ抜粋・要約なし）：
```
diff --git a/app/Policies/ReadingPlanPolicy.php b/app/Policies/ReadingPlanPolicy.php
index e6b4c97..c9d3cc3 100644
--- a/app/Policies/ReadingPlanPolicy.php
+++ b/app/Policies/ReadingPlanPolicy.php
@@ -13,7 +13,8 @@ class ReadingPlanPolicy
      */
     public function update(User $user, ReadingPlan $plan): bool
     {
-        return $user->id === $plan->user_id;
+        return $user->id === $plan->user_id
+            && $plan->status !== ReadingPlanStatus::Completed;
     }

     /**
```
（差分ハンク `@@ -13,7 +13,8 @@` は update() メソッドの1箇所のみ。complete()・delete() を示すハンクは出力に含まれない。ファイル側の変更行数は git show --stat で `3 ++-`。）

### 2. 現在のポリシー全文（update / delete / complete 各メソッド本文）

実行コマンド：
```
$ Read app/Policies/ReadingPlanPolicy.php
```

出力（全文）：
```php
<?php

namespace App\Policies;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Models\User;

class ReadingPlanPolicy
{
    /**
     * Determine whether the user can update the reading plan.
     */
    public function update(User $user, ReadingPlan $plan): bool
    {
        return $user->id === $plan->user_id
            && $plan->status !== ReadingPlanStatus::Completed;
    }

    /**
     * Determine whether the user can delete the reading plan.
     */
    public function delete(User $user, ReadingPlan $plan): bool
    {
        return $user->id === $plan->user_id;
    }

    /**
     * Determine whether the user can mark the reading plan as completed.
     */
    public function complete(User $user, ReadingPlan $plan): bool
    {
        return $user->id === $plan->user_id
            && $plan->status !== ReadingPlanStatus::Completed;
    }
}
```

### 3. edit() / update() が authorize('update') 経由か

実行コマンド：
```
$ grep -n "authorize" app/Http/Controllers/ReadingPlanController.php
```

出力：
```
64:        $this->authorize('update', $plan);
74:        $this->authorize('update', $plan);
87:        $this->authorize('complete', $plan);
103:        $this->authorize('delete', $plan);
```

該当メソッド本文（Read, 58-96行）：
```php
    public function edit(ReadingPlan $plan): View
    {
        $this->authorize('update', $plan);

        return view('reading-plans.edit', ['readingPlan' => $plan]);
    }

    /**
     * 読書計画の期日を更新する（target_dateのみ）。
     */
    public function update(ReadingPlanUpdateRequest $request, ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('update', $plan);

        $plan->update(['target_date' => $request->validated('target_date')]);

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を更新しました');
    }

    /**
     * 読書計画を完了状態にする。
     */
    public function complete(ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('complete', $plan);
        ...
    }
```
（edit=64行・update=74行が共に authorize('update', $plan)。complete=87行は authorize('complete')、destroy=103行は authorize('delete')。）

### 4. 追加テスト2本の実行と本文（実HTTPで403 assert）

実行コマンド：
```
$ ./vendor/bin/sail artisan test --filter 'test_edit_on_completed_plan_forbidden_even_for_owner|test_update_on_completed_plan_forbidden_even_for_owner'
```

出力：
```
   PASS  Tests\Feature\ReadingPlanTest
  ✓ edit on completed plan forbidden even for owner                      1.02s
  ✓ update on completed plan forbidden even for owner                    0.02s

  Tests:    2 passed (4 assertions)
  Duration: 1.09s
```

テスト本文（Read, 236-257行）：
```php
    /** R29: 本人でも完了済み計画のedit（GET）は403（URL直打ち防御・所有者チェックとは分離） */
    public function test_edit_on_completed_plan_forbidden_even_for_owner(): void
    {
        $user = User::factory()->create();
        $plan = $this->makePlan($user, Book::factory()->create(), ReadingPlanStatus::Completed, now()->subDays(1)->toDateString(), now()->toDateTimeString());

        $this->actingAs($user)->get("/reading-plans/{$plan->id}/edit")->assertForbidden();
    }

    /** R29: 本人でも完了済み計画のupdate（PUT）は403で、値も変わらない */
    public function test_update_on_completed_plan_forbidden_even_for_owner(): void
    {
        $user = User::factory()->create();
        $originalDate = now()->subDays(1)->toDateString();
        $plan = $this->makePlan($user, Book::factory()->create(), ReadingPlanStatus::Completed, $originalDate, now()->toDateTimeString());

        $this->actingAs($user)->put("/reading-plans/{$plan->id}", ['target_date' => now()->addDays(9)->toDateString()])
            ->assertForbidden();

        $fresh = $plan->fresh();
        $this->assertSame($originalDate, $fresh->target_date->toDateString());
        $this->assertSame(ReadingPlanStatus::Completed, $fresh->status);
    }
```
（edit側は `actingAs($user)->get(".../edit")->assertForbidden()`、update側は `actingAs($user)->put(...)->assertForbidden()` の実HTTP。Blade非表示を assertDontSee 等で代替する記述は本文に含まれない。update側は plan->fresh() で target_date と status が変わらないことも assert。makePlan の第3引数 ReadingPlanStatus::Completed でレコードを完了状態にしている。）

### 5. 全体テスト末尾

実行コマンド：
```
$ ./vendor/bin/sail artisan test
```

末尾出力：
```
   PASS  Tests\Feature\WebErrorPageTest
  ✓ web 404 returns japanese page                                        0.01s
  ✓ web 403 returns japanese page                                        0.01s
  ✓ web 419 returns japanese page                                        0.01s
  ✓ api 404 returns json                                                 0.01s
  ✓ api 401 returns json                                                 0.01s
  ✓ api 403 returns json                                                 0.01s

  Tests:    169 passed (562 assertions)
  Duration: 4.31s
```

### 6. Pint

実行コマンド：
```
$ ./vendor/bin/sail bin pint --test app/Policies/ReadingPlanPolicy.php tests/Feature/ReadingPlanTest.php
```

出力：
```
  ..

  ──────────────────────────────────────────────────────────────────── Laravel
    PASS   ........................................................... 2 files
```

### 7. 回帰の目視（ReadingPlanTest 群の各行）

実行コマンド：
```
$ ./vendor/bin/sail artisan test --filter 'ReadingPlanTest'
```

出力（全行）：
```
   PASS  Tests\Feature\ReadingPlanTest
  ✓ store success                                                        1.04s
  ✓ update success                                                       0.02s
  ✓ complete success                                                     0.01s
  ✓ destroy success                                                      0.01s
  ✓ duplicate in progress plan rejected                                  0.01s
  ✓ can create when existing plan completed                              0.01s
  ✓ can create when existing plan expired                                0.01s
  ✓ update changes only target date                                      0.02s
  ✓ other users plan edit forbidden                                      0.02s
  ✓ other users plan update forbidden                                    0.02s
  ✓ other users plan complete forbidden                                  0.01s
  ✓ other users plan destroy forbidden                                   0.01s
  ✓ complete on already completed plan forbidden                         0.01s
  ✓ edit on completed plan forbidden even for owner                      0.01s
  ✓ update on completed plan forbidden even for owner                    0.01s
  ✓ index without status shows all own plans                             0.02s
  ✓ index with valid status filters                                      0.02s
  ✓ index with undefined status returns empty                            0.01s
  ✓ create form displayed                                                0.02s
  ✓ owner can view edit form                                             0.02s

  Tests:    20 passed (60 assertions)
  Duration: 1.37s
```
（test_update_success = "update success"、test_complete_success = "complete success"、test_other_users_plan_edit/update/complete/destroy_forbidden = "other users plan ... forbidden" の4行が出力に含まれる。）

## 未確認・保留
- 要件シート.xlsx シート10 R29 の原文文言は、本検品では xlsx を直接開いておらず、コミットメッセージ・テストコメント内の引用（「完了済み計画は編集不可」「GET /reading-plans/{plan}/edit へ直接アクセスしても編集操作の対象にならない」に対応する挙動）のみを確認した。xlsx セルの生文字列との一致は未確認。
- edit(GET) 側テストは 403（assertForbidden）のみを確認しており、update(PUT) 側のような「レコード無変更」に相当する副作用不在の追加 assert は edit 側には存在しない（GET のため状態変更経路自体がないことによる）。

## worktree情報
- ブランチ名：fix/book-search-and-isbn-ui
- 本体へのマージ：未（main へのマージは片倉の判定確定後。次工程は本証拠を片倉／チャット側が突き合わせ判定 → 全行 YES 確定後にマージ）

## 判定はしない
本ファイルは YES/NO・PASS/FAIL の判定を含まない。判定は片倉／チャット側がこの証拠を見て行う。
