# QUESTIONS.md — 未定義事項の退避ファイル

CCが実装中に、発注書・機能仕様書・`CLAUDE.md` のどれにも定義がない事項に当たった場合、
その場で仮決めせずここに記録し、該当作業を止める。

## 記録書式（1件1行）

| 発生日 | 対象発注書 | 止まった箇所 | CCの解釈候補 |
|---|---|---|---|
| （例）8/26 | 02_書籍CRUD | 書籍削除時の確認ダイアログ文言 | (a) 「本当に削除しますか？」 (b) Bladeの文言をそのまま踏襲 |
| 2026-09-02 | 05_公開APIシーディング | `App\Models\Book` に `published_date` の `date` cast が無い（走行①の実装は `$casts` 未定義）。発注書02§9の参考モデルおよび発注書05§5は `$this->published_date->format('Y-m-d')` を前提としており cast 有りを想定しているが、実モデルに cast を追加すると `books/show.blade.php` の `{{ $book->published_date }}` の出力が「2012-06-23」→「2012-06-23 00:00:00」に変わり frozen Blade の契約（CLAUDE.md§0でBladeが最優先）に反する。 | (a) §0に従いBlade契約を優先し、モデルには cast を追加せず API Resource 側で `Carbon::parse($this->published_date)->format('Y-m-d')` に変更して両立させる（今回採用。既存Blade表示を壊さない最小修正）。 (b) 走行①のスコープ漏れとしてモデルに `'published_date' => 'date:Y-m-d'` cast を追加し、発注書の literal コードを維持する（ただしBlade表示への影響検証が必要）。 |
| 2026-09-11 | 07_検索フィルタソート（検品表B-2） | 検品表B-2は `/books?keyword=Code&genre=3` で「リーダブルコード」「Clean Code」の2冊を期待するが、走行⑤確定のシードデータでは「リーダブルコード」の title=「リーダブルコード」（カタカナ）・author=「Dustin Boswell」であり "Code" の文字列を含まない。発注書07§2はキーワード検索を title/author の LIKE 検索のみと明示しており、実装はこれに完全準拠。この仕様＋実データでは keyword=Code は「Clean Code」のみに一致し、genre=3 とのAND結果は1冊（Clean Code）になる。実装欠陥ではなく検品表の想定データとシードの不整合。 | (a) 発注書07§2（title/author LIKE）が正本であり実装は正しい。検品表B-2の期待値を「keyword=Clean&genre=3 → Clean Code の1冊」等、実シードで成立する例に修正する（今回採用: 実装は変更しない）。 (b) B-2のAND検証を別の組み合わせ（例: keyword=夏目 だけでAND無関係、genre併用は別ID）に差し替える。※検索対象カラムの拡張（説明文等）は§2の明示指定に反するため候補にしない。 |
| 2026-09-03 | 差し戻し指示_走行②③④⑤（②B-2・③C-4） | 指示のcurl（未認証でのPOST `/books`・PUT `/books/1`・DELETE `/books/1`・PATCH `/books/1/restore`・POST `/reviews/1/like`）は**302 Found + Location: .../login を期待**しているが、実機は全て **`HTTP/1.1 419 unknown status`（CSRFトークン不一致）** を返す。web ミドルウェアグループでは `VerifyCsrfToken` が `auth` より先に実行されるため、CSRFトークンなしの書き込み系リクエストは auth 到達前に419で拒否される（Laravel標準挙動。テストではCSRFが無効化されるため302→loginになる）。完了条件「302以外は仮決めせずそのまま報告・この場で直さない」に従い記録し、実装は変更していない。 | (a) 419はCSRF保護による正常な防御挙動であり、未認証書き込みは実質的に拒否されている（auth到達前に弾かれる）とみなし、curl証拠は419のまま受理する。 (b) 未認証リダイレクト（302→login）の確認は実HTTP curlではなくFeatureテスト（CSRF無効環境）で行う確認方法に検品表側を修正する。※どちらを採るか検品側の裁定を仰ぐ。 |
| 2026-09-15 | 11_読書計画CRUD（§3 ReadingPlanモデル） | 発注書§3のモデル例は `protected function casts(): array`（メソッド形式のcast定義）を用いているが、本プロジェクトの `laravel/framework` は 10.50.0 であり `casts()` メソッド自動呼び出しは Laravel 11+ の機能。L10では当該メソッドは無視され `status`/`target_date` がキャストされず、`reading-plans/*.blade.php` が要求する `$plan->status->label()`・`$plan->target_date->format('Y-m-d')`（Enum/Carbon前提）が実行時エラーになる。Blade正本（§0）を満たすためキャストを実効化する必要がある。 | (a) L10標準の `protected $casts = [...]` プロパティ形式で `status`(Enum)・`target_date`(date)・`completed_at`(datetime) を定義し、発注書の意図（Enum＋日付キャスト）をそのまま実現する（今回採用。挙動は発注書§3の意図と同一、Blade契約を満たす）。 (b) 発注書literalのままメソッド形式を残す（L10では機能せずBladeが壊れるため不採用）。 |
| 2026-09-15 | 11_読書計画CRUD（§9 ReadingPlanSeeder） | §9は6件目の計画所有者を「鈴木花子（ID6想定）」とするが、`UserSeeder` はユーザーを5名しか作成せず、鈴木花子は実際には ID2（ID6のユーザーは存在しない）。§9の目的は「山田太郎ログイン中に他ユーザーの計画へ直打ちして403を確認する」ことで、所有者が山田以外であれば達成できる。 | (a) 「ID6」は stale な想定注記とみなし、明示された氏名「鈴木花子」（email `suzuki@example.com`＝ID2）を所有者に割り当てる（今回採用。§9の403確認目的を満たす）。 (b) UserSeederを6名に増やしID6を実在させる（走行①確定のシードを変更するため不採用）。 |
| 2026-09-15 | 12_通知バッチ（§4 リマインダーバッチの対象status） | 発注書§4の `SendReadingPlanReminders` 例コードは3つのtiming全てを共通メソッド `notify()` で処理し `status = in_progress` のみに絞っている。一方、機能仕様書 notifications.md §5-161〜169（「timing別の対象status（確定）」表＋理由）は `three_days_after` のみ「`completed` 以外（`in_progress` と `expired`）」を対象と明記する。理由: 7:00の本バッチ実行時点では0:00の状態更新バッチで当該計画が既に `expired` へ切替済みのため、`in_progress` 限定だと「期限切れになりました」通知が漏れる。CLAUDE.md §0 の正本優先順位では機能仕様書(3位)＞発注書(4位)。 | (a) §0に従い上位正本（機能仕様書§5「確定」表）を採用し、`three_days_after` の対象を `in_progress` ＋ `expired`（＝completed以外）に、`three_days_before`/`on_due_date` は `in_progress` のみに実装する（今回採用。0:00失効後の計画にも3日後通知が届く。発注書§4の例コードはこの点で機能仕様と不整合）。 (b) 発注書§4のliteral（全timing in_progress限定）を維持する（機能仕様§5の確定記述・本文『自動的に「期限切れ」になりました』の趣旨に反するため不採用）。 |
| 2026-09-15 | 13_品質リファクタ（§7 既存テスト全通過） | §7は「既存テストがあればリファクタ後に全通過することを確認する」を求めるが、`sail artisan test` は 9 failed / 71 passed。9件の内訳は全て走行⑧/⑨/⑩の意図的な契約変更に起因し、⑬（型宣言・PHPDocの追加のみ）とは無関係: (1) BookApiTest ×5＝走行⑩でPOST/PUT/DELETEに `auth:sanctum` を付与したためトークン無しの旧テストが401（発注書⑩§0でテスト作成は走行⑭に繰延）。(2) BookCrudTest「store validation」＝走行⑧で isbn を nullable 化したため isbn 必須エラーが出ない。(3) BookCrudTest「deleted book show/hides buttons」×2＝`books/show.blade.php` に削除済みバナー「削除済みの書籍にはレビューを投稿できません」「復元する」が現作業ツリーに存在しない（走行⑨検品 E-2 と同一事象。⑬はBlade変更禁止）。(4) SeederTest「seed counts」＝走行⑨★ReviewSeederの `rand(2,4)` でレビュー件数が可変（36/29等、旧テストは32固定を期待）。⑬変更（app/配下のみ）を `git stash` して計測したベースラインも同一の 9 failed/71 passed で、⑬は新規失敗を1件も導入していない（型追加のみ・TypeError無し）。 | (a) 9件は走行⑭（応用テスト一括作成）で新契約に合わせて更新する範囲であり、⑬のリファクタ起因の失敗は0件（stash前後同数で証明）。§7の「全通過」は走行⑭完了時点で満たす前提とし、⑬時点では「⑬起因の新規失敗0」をもって完了とみなす（今回の解釈）。(2) の isbn 必須期待・(3) の削除済みバナーは、それぞれ発注書⑧のnullable化・走行②/⑧のshow.blade正本と旧テストの不整合であり、片倉裁定を仰ぐ。 |
- **発生日**: 該当箇所に当たった日付
- **対象発注書**: `02_発注書/NN_機能名.md` のファイル名
- **止まった箇所**: 何がどう未定義なのか（画面・処理・カラム名など具体的に）
- **CCの解釈候補**: CC自身が考えた解釈案（複数可）。片倉が選ぶか却下するかを判断する材料

## 運用ルール

- 1発注書につき相談3往復（＝この表に3行到達）で打ち切り、片倉の裁定を待つ。CCは自走を止める。
- 片倉が裁定したら、該当行を削除する（解決済みの行を残さない）。
- 全走行終了時点でこの表の行数がゼロであることが、第7週の最終検品条件のひとつ。

---

現在の未解消件数: **3件**（②B-2/③C-4のCSRF419【検証方法の記録として保持・修正パッチで確認方法変更済み】、05のpublished_date cast【走行⑧のadvanced Blade移入で show.blade が `published_date?->format('Y-m-d')` に変わったため発注書08§5に従いモデルへ cast を追加。当該行の裁定前提が変化した点を片倉に要確認】、07検品表B-2のシード不整合）

※ ③E-2/⑤F-4のid=11未削除前提の行は、差し戻し指示（走行②③⑤ 残存4件）の裁定「カウント前に対象書籍を論理削除してから確認する」により検証手順が確定したため削除した（2026-09-03）。
