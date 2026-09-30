# FormRequest使用箇所とテストカテゴリ対応 調査

## やったこと（1〜3行、事実のみ）
app/Http/Requests 配下の全FormRequestと、それを引数型に持つコントローラーメソッドを grep で列挙した。
tests/Feature・tests/Unit の全テストメソッドを列挙し、指定22カテゴリに振り分けた。`./vendor/bin/sail artisan test` を実行した。

## 変更ファイル
```
$ git diff --stat
（出力なし）
```

## 証拠（生の実行結果のみ）

### 0. ブランチとHEAD
```
$ git branch --show-current && git rev-parse --short HEAD
main
7c25dcd
```

### 1. FormRequest 一覧
```
$ find app/Http/Requests -name "*.php" | sort
app/Http/Requests/Api/V1/ApiFormRequest.php
app/Http/Requests/Api/V1/IndexBookRequest.php
app/Http/Requests/Api/V1/StoreApiBookRequest.php
app/Http/Requests/Api/V1/UpdateApiBookRequest.php
app/Http/Requests/ReadingPlanStoreRequest.php
app/Http/Requests/ReadingPlanUpdateRequest.php
app/Http/Requests/StoreBookRequest.php
app/Http/Requests/StoreGenreRequest.php
app/Http/Requests/StoreReviewRequest.php
app/Http/Requests/UpdateBookRequest.php
app/Http/Requests/UpdateGenreRequest.php
app/Http/Requests/UpdateReviewRequest.php
```

| FormRequest（パス） | 継承元クラス | 使っているコントローラー@メソッド（ファイル:行） |
|---|---|---|
| app/Http/Requests/Api/V1/ApiFormRequest.php（abstract） | FormRequest | 該当なし（grep -rnw ApiFormRequest app/Http/Controllers の出力なし。IndexBook/StoreApiBook/UpdateApiBookRequest の親） |
| app/Http/Requests/Api/V1/IndexBookRequest.php | ApiFormRequest | Api\V1\BookController@index（app/Http/Controllers/Api/V1/BookController.php:23） |
| app/Http/Requests/Api/V1/StoreApiBookRequest.php | ApiFormRequest | Api\V1\BookController@store（app/Http/Controllers/Api/V1/BookController.php:61） |
| app/Http/Requests/Api/V1/UpdateApiBookRequest.php | ApiFormRequest | Api\V1\BookController@update（app/Http/Controllers/Api/V1/BookController.php:78） |
| app/Http/Requests/ReadingPlanStoreRequest.php | FormRequest | ReadingPlanController@store（app/Http/Controllers/ReadingPlanController.php:46） |
| app/Http/Requests/ReadingPlanUpdateRequest.php | FormRequest | ReadingPlanController@update（app/Http/Controllers/ReadingPlanController.php:72） |
| app/Http/Requests/StoreBookRequest.php | FormRequest | BookController@store（app/Http/Controllers/BookController.php:128） |
| app/Http/Requests/StoreGenreRequest.php | FormRequest | GenreController@store（app/Http/Controllers/GenreController.php:34） |
| app/Http/Requests/StoreReviewRequest.php | FormRequest | ReviewController@store（app/Http/Controllers/ReviewController.php:18） |
| app/Http/Requests/UpdateBookRequest.php | FormRequest | BookController@update（app/Http/Controllers/BookController.php:170） |
| app/Http/Requests/UpdateGenreRequest.php | FormRequest | GenreController@update（app/Http/Controllers/GenreController.php:62） |
| app/Http/Requests/UpdateReviewRequest.php | FormRequest | ReviewController@update（app/Http/Controllers/ReviewController.php:40） |

指定2か所の引数行（原文）:
```
app/Http/Controllers/Api/V1/BookController.php:78:    public function update(UpdateApiBookRequest $request, Book $book): BookResource
app/Http/Controllers/ReviewController.php:40:    public function update(UpdateReviewRequest $request, Review $review): RedirectResponse
```

### 2. テストのカテゴリ対応
振り分けはファイル名・メソッド名・テスト本文からの調査者の読み取りによる。

| カテゴリ | テストファイル | テストメソッド名（全件） | メソッド数 |
|---|---|---|---|
| 単体: モデル | tests/Unit/BookTest.php | test_book_belongs_to_user, test_book_belongs_to_many_genres, test_book_has_many_reviews, test_reviews_avg_rating, test_soft_delete_exclusion | 5 |
| 単体: モデル | tests/Unit/ReviewTest.php | test_review_belongs_to_book, test_review_belongs_to_user, test_review_liked_by_users, test_review_book_uses_with_trashed | 4 |
| 単体: モデル | tests/Unit/GenreTest.php | test_genre_belongs_to_many_books | 1 |
| 単体: モデル | tests/Unit/FavoriteTest.php | test_favorite_books_toggle | 1 |
| 単体: モデル | tests/Unit/ModelRelationTest.php | test_book_favorited_by_users, test_user_books, test_user_reviews, test_user_reading_plans | 4 |
| 機能: 画面アクセス | tests/Feature/ScreenAccessTest.php | test_authenticated_user_redirected_from_guest_pages, test_guest_redirected_from_auth_pages, test_public_pages_accessible_by_guest, test_intended_url_after_login, test_guest_cannot_perform_book_write_actions | 5 |
| 機能: 書籍CRUD | tests/Feature/BookCrudTest.php | test_store_book_success, test_store_validation_errors, test_genre_sync_on_store_and_update, test_edit_authorization, test_delete_authorization, test_deleted_book_excluded_from_listings, test_deleted_book_show_page, test_deleted_book_hides_buttons, test_deleted_book_reviews_still_visible, test_restore_book, test_restore_authorization, test_deleted_isbn_uniqueness, test_reviews_kept_after_book_soft_delete, test_update_ignores_own_isbn, test_restore_undeleted_book_forbidden, test_edit_form_shows_published_date_in_date_input_format, test_index_shows_not_found_message_when_search_has_no_result | 17 |
| 機能: レビュー | tests/Feature/ReviewTest.php | test_store_review, test_store_review_without_comment, test_store_review_requires_rating, test_edit_authorization, test_delete_authorization, test_review_delete_cascades_likes, test_review_operations_on_deleted_book | 7 |
| 機能: ジャンル | tests/Feature/GenreTest.php | test_genre_screens_render, test_store_success, test_store_requires_name, test_store_unique_name, test_edit_allowed_for_any_authenticated_user, test_destroy_blocked_when_books_attached, test_destroy_blocked_when_only_trashed_book_attached, test_destroy_success_when_no_books, test_db_constraint_prevents_delete, test_index_shows_zero_count_for_genre_with_only_trashed_books, test_show_paginates_books_at_10 | 11 |
| 機能: お気に入り | tests/Feature/FavoriteTest.php | test_toggle_on, test_toggle_off, test_toggle_has_no_flash, test_guest_redirected_from_favorites, test_index_shows_only_own_favorites, test_index_ordered_by_created_at_desc, test_deleted_book_excluded_and_restored_book_reappears | 7 |
| 機能: いいね | tests/Feature/ReviewLikeTest.php | test_like_toggle_on_off, test_like_on_deleted_book_review, test_guest_cannot_like_review | 3 |
| 機能: ランキング | tests/Feature/RankingTest.php | test_ranking_ordered_by_avg_rating_desc, test_ranking_excludes_books_without_reviews, test_ranking_excludes_deleted_books, test_ranking_public | 4 |
| 機能: 認証 | tests/Feature/AuthTest.php | test_registration_logs_in_and_redirects, test_registration_validation_errors, test_login_success, test_login_failure_message, test_logout_requires_login_again | 5 |
| 機能: 公開API | tests/Feature/Api/BookApiTest.php | test_index_is_public_returns_200, test_show_is_public_returns_200, test_index_keyword_filter, test_index_genre_filter, test_store_success_with_sanctum, test_update_success_with_sanctum, test_destroy_success_with_sanctum, test_store_unauthenticated_returns_401, test_update_unauthenticated_returns_401, test_destroy_unauthenticated_returns_401, test_update_other_users_book_returns_403, test_destroy_other_users_book_returns_403, test_show_not_found_returns_json_404, test_show_soft_deleted_returns_404, test_store_validation_error_when_authenticated | 15 |
| 機能: 公開API | tests/Feature/WebErrorPageTest.php | test_api_404_returns_json, test_api_401_returns_json, test_api_403_returns_json | 3 |
| ★機能: 検索・フィルタ | tests/Feature/BookSearchTest.php | test_keyword_partial_match_returns_only_matching_books, test_genre_filter_returns_only_matching_books, test_search_condition_preserved_across_pagination, test_soft_deleted_book_excluded_from_search, test_search_is_public | 5 |
| ★機能: 検索・フィルタ | tests/Feature/BookFormUiTest.php | test_index_renders_search_form, test_search_form_retains_input_values | 2 |
| ★機能: 検索・フィルタ | tests/Feature/Api/BookApiTest.php | test_index_keyword_filter, test_index_genre_filter | 2 |
| ★機能: 検索・フィルタ | tests/Feature/BookCrudTest.php | test_index_shows_not_found_message_when_search_has_no_result | 1 |
| ★機能: ソート | tests/Feature/BookSearchTest.php | test_sort_returns_books_in_specified_order, test_sort_oldest_returns_ascending_by_created_at, test_sort_rating_returns_by_average_rating_desc | 3 |
| ★機能: 書籍CRUD（認可の詳細テスト） | tests/Feature/BookCrudAdvancedTest.php | test_create_form_displayed_with_genres, test_create_form_requires_authentication, test_store_succeeds_with_empty_isbn_and_published_date, test_update_succeeds_with_empty_isbn_and_published_date, test_isbn_format_validated_only_when_present, test_isbn_uniqueness_validated_only_when_present, test_published_date_format_validated_only_when_present, test_empty_post_does_not_require_isbn_or_published_date, test_other_user_cannot_view_edit, test_other_user_cannot_update, test_other_user_cannot_delete | 11（うち認可を名に含むもの: test_create_form_requires_authentication, test_other_user_cannot_view_edit, test_other_user_cannot_update, test_other_user_cannot_delete の4） |
| ★機能: ISBN検索 | tests/Feature/IsbnSearchTest.php | test_isbn_13_digits_success_with_http_fake, test_isbn_12_digits_returns_422, test_isbn_14_digits_returns_422, test_isbn_non_numeric_returns_422, test_isbn_not_found_returns_404, test_isbn_upstream_failure_returns_502, test_isbn_connection_exception_returns_502, test_api_key_is_sent_when_configured, test_api_key_absent_when_not_configured, test_published_date_year_only_is_padded_to_january_first, test_published_date_year_month_is_padded_to_first_day, test_published_date_full_date_is_returned_as_is, test_published_date_missing_returns_empty, test_published_date_unknown_format_returns_empty | 14 |
| ★機能: ISBN検索 | tests/Feature/BookFormUiTest.php | test_create_renders_isbn_autofill_section | 1 |
| ★機能: マイ読書レポート | tests/Feature/ReadingReportTest.php | test_report_shows_all_statistics, test_books_read_counts_unique_books_not_review_count, test_soft_deleted_book_review_included_in_report, test_report_requires_authentication | 4 |
| ★機能: Sanctum認証 | tests/Feature/Api/BookApiTest.php | test_store_success_with_sanctum, test_update_success_with_sanctum, test_destroy_success_with_sanctum, test_store_unauthenticated_returns_401, test_update_unauthenticated_returns_401, test_destroy_unauthenticated_returns_401, test_update_other_users_book_returns_403, test_destroy_other_users_book_returns_403, test_store_validation_error_when_authenticated | 9 |
| ★機能: Sanctum認証 | tests/Feature/WebErrorPageTest.php | test_api_401_returns_json, test_api_403_returns_json（本文で postJson 未認証→401 / Sanctum::actingAs 他人→403） | 2 |
| ★機能: 読書計画 | tests/Feature/ReadingPlanTest.php | test_store_success, test_update_success, test_complete_success, test_destroy_success, test_duplicate_in_progress_plan_rejected, test_can_create_when_existing_plan_completed, test_can_create_when_existing_plan_expired, test_update_changes_only_target_date, test_other_users_plan_edit_forbidden, test_other_users_plan_update_forbidden, test_other_users_plan_complete_forbidden, test_other_users_plan_destroy_forbidden, test_complete_on_already_completed_plan_forbidden, test_edit_on_completed_plan_forbidden_even_for_owner, test_update_on_completed_plan_forbidden_even_for_owner, test_index_without_status_shows_all_own_plans, test_index_with_valid_status_filters, test_index_with_undefined_status_returns_empty, test_create_form_displayed, test_owner_can_view_edit_form | 20 |
| ★機能: 読書計画 | tests/Unit/ReadingPlanStatusTest.php（Enum ReadingPlanStatus の単体テスト。extends PHPUnit\Framework\TestCase） | test_label_for_each_case, test_badge_class_for_each_case, test_enum_values | 3 |
| ★機能: 読書計画（期限変更） | tests/Feature/ReadingPlanTest.php | test_update_success, test_update_changes_only_target_date, test_other_users_plan_edit_forbidden, test_other_users_plan_update_forbidden, test_edit_on_completed_plan_forbidden_even_for_owner, test_update_on_completed_plan_forbidden_even_for_owner, test_owner_can_view_edit_form | 7 |
| ★機能: リマインダーバッチ | tests/Feature/ReadingPlanReminderTest.php | test_three_days_before_and_on_due_date_target_in_progress_only, test_three_days_after_targets_in_progress_and_expired, test_plan3_double_scenario | 3 |
| ★機能: リマインダーバッチ | tests/Feature/ScheduleTest.php | test_reminders_scheduled_at_seven | 1 |
| ★機能: リマインダーバッチ | tests/Feature/NotificationTest.php（通知一覧画面・既読化。リマインダーの出力先） | test_index_shows_own_notifications_newest_first, test_owner_can_mark_notification_read, test_other_user_cannot_mark_notification_read, test_guest_redirected_from_notifications | 4 |
| ★機能: 自動失効バッチ | tests/Feature/ExpireReadingPlansTest.php | test_exactly_three_days_ago_in_progress_becomes_expired, test_four_days_ago_in_progress_stays_in_progress, test_two_days_ago_today_and_future_stay_in_progress, test_completed_plan_unchanged | 4 |
| ★機能: 自動失効バッチ | tests/Unit/ExpireReadingPlansBoundaryTest.php | test_boundary（@dataProvider boundaryProvider: '3日前ちょうど→失効' / '4日前→据え置き' / '2日前→据え置き' ほか） | 1（データセット複数） |
| ★機能: 自動失効バッチ | tests/Feature/ScheduleTest.php | test_expire_scheduled_at_midnight | 1 |

どのカテゴリにも入らなかったファイル:
| テストファイル | テストメソッド名 | メソッド数 |
|---|---|---|
| tests/Feature/SeederTest.php | test_seed_counts, test_seed_is_idempotent | 2 |
| tests/Feature/WebErrorPageTest.php（一部） | test_web_404_returns_japanese_page, test_web_403_returns_japanese_page, test_web_419_returns_japanese_page | 3 |

ファイル別メソッド数（`grep -cE 'public function test'`）:
```
=== tests/Feature/Api/BookApiTest.php (15)
=== tests/Feature/AuthTest.php (5)
=== tests/Feature/BookCrudAdvancedTest.php (11)
=== tests/Feature/BookCrudTest.php (17)
=== tests/Feature/BookFormUiTest.php (3)
=== tests/Feature/BookSearchTest.php (8)
=== tests/Feature/ExpireReadingPlansTest.php (4)
=== tests/Feature/FavoriteTest.php (7)
=== tests/Feature/GenreTest.php (11)
=== tests/Feature/IsbnSearchTest.php (14)
=== tests/Feature/NotificationTest.php (4)
=== tests/Feature/RankingTest.php (4)
=== tests/Feature/ReadingPlanReminderTest.php (3)
=== tests/Feature/ReadingPlanTest.php (20)
=== tests/Feature/ReadingReportTest.php (4)
=== tests/Feature/ReviewLikeTest.php (3)
=== tests/Feature/ReviewTest.php (7)
=== tests/Feature/ScheduleTest.php (2)
=== tests/Feature/ScreenAccessTest.php (5)
=== tests/Feature/SeederTest.php (2)
=== tests/Feature/WebErrorPageTest.php (6)
=== tests/Unit/BookTest.php (5)
=== tests/Unit/ExpireReadingPlansBoundaryTest.php (1)
=== tests/Unit/FavoriteTest.php (1)
=== tests/Unit/GenreTest.php (1)
=== tests/Unit/ModelRelationTest.php (4)
=== tests/Unit/ReadingPlanStatusTest.php (3)
=== tests/Unit/ReviewTest.php (4)
```
合計 174 メソッド（test_ 接頭辞。#[Test]/@test 注釈付きメソッドは grep で0件）。

### テスト実行
```
$ ./vendor/bin/sail artisan test 2>&1 | tail -8
Sail is not running.

You may Sail using the following commands: './vendor/bin/sail up' or './vendor/bin/sail up -d'
```

## 未確認・保留
- `sail artisan test` の通過件数行は取得できていない（Sail 未起動。起動は指示範囲外のため行っていない）。
- カテゴリ振り分けは調査者の読み取り。特に NotificationTest（リマインダーに含めた）、ReadingPlanStatusTest（読書計画に含めた）、WebErrorPageTest の API 3件（公開API/Sanctum認証に含めた）、BookCrudAdvancedTest（ISBN・出版日の任意項目検証7件を含む）は振り分けの判断が入っている。

## worktree情報
- ブランチ名：main（HEAD 7c25dcd）
- 本体へのマージ：該当なし（読み取り調査のみ、コード変更なし）

## 判定はしない
本ファイルは YES/NO・PASS/FAIL の判定を含まない。判定は片倉／チャット側がこの証拠を見て行う。
