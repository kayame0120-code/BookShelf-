# 抽出台帳 U 未参照ファイル（check_cover.py が自動追加）

- 記入規則: docs/02_発注書/98_実装仕様抽出調査.md の §6
- ブロック・F行の削除、並べ替え、F行本文の編集は禁止。「事実:」行の記入と追加のみ行う。

---

### U001 app/Http/Controllers/Controller.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: app/Http/Controllers/Controller.php:3 namespace App\Http\Controllers;
  - 事実: app/Http/Controllers/Controller.php:7 use Illuminate\Routing\Controller as BaseController;
  - 事実: app/Http/Controllers/Controller.php:9 class Controller extends BaseController
  - 事実: app/Http/Controllers/Controller.php:5 use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
  - 事実: app/Http/Controllers/Controller.php:6 use Illuminate\Foundation\Validation\ValidatesRequests;
  - 事実: app/Http/Controllers/Controller.php:11 use AuthorizesRequests, ValidatesRequests;
  - 事実: app/Http/Controllers/Controller.php:9-12 クラス本体はトレイト使用行（11行目）のみで、メソッド・プロパティの定義は0件（ファイル全12行）
  - 事実: S220 app/Http/Controllers/Api/V1/BookController.php:18:class BookController extends Controller
  - 事実: S220 app/Http/Controllers/BookController.php:18:class BookController extends Controller
  - 事実: S220 app/Http/Controllers/FavoriteController.php:10:class FavoriteController extends Controller
  - 事実: S220 app/Http/Controllers/GenreController.php:11:class GenreController extends Controller
  - 事実: S220 app/Http/Controllers/NotificationController.php:10:class NotificationController extends Controller
  - 事実: S220 app/Http/Controllers/RankingController.php:8:class RankingController extends Controller
  - 事実: S220 app/Http/Controllers/ReadingPlanController.php:15:class ReadingPlanController extends Controller
  - 事実: S220 app/Http/Controllers/ReportController.php:11:class ReportController extends Controller
  - 事実: S220 app/Http/Controllers/ReviewController.php:13:class ReviewController extends Controller
  - 事実: S223 app/Http/Controllers/Api/V1/BookController.php:5:use App\Http\Controllers\Controller;
  - 事実: S221 app/Http/Controllers/Api/V1/BookController.php:80:        $this->authorize('update', $book);
  - 事実: S221 app/Http/Controllers/Api/V1/BookController.php:97:        $this->authorize('delete', $book);
  - 事実: S221 app/Http/Controllers/BookController.php:160:        $this->authorize('update', $book);
  - 事実: S221 app/Http/Controllers/BookController.php:172:        $this->authorize('update', $book);
  - 事実: S221 app/Http/Controllers/BookController.php:187:        $this->authorize('delete', $book);
  - 事実: S221 app/Http/Controllers/BookController.php:199:        $this->authorize('restore', $book);
  - 事実: S221 app/Http/Controllers/ReadingPlanController.php:64:        $this->authorize('update', $plan);
  - 事実: S221 app/Http/Controllers/ReadingPlanController.php:74:        $this->authorize('update', $plan);
  - 事実: S221 app/Http/Controllers/ReadingPlanController.php:87:        $this->authorize('complete', $plan);
  - 事実: S221 app/Http/Controllers/ReadingPlanController.php:103:        $this->authorize('delete', $plan);
  - 事実: S221 app/Http/Controllers/ReviewController.php:30:        $this->authorize('update', $review);
  - 事実: S221 app/Http/Controllers/ReviewController.php:42:        $this->authorize('update', $review);
  - 事実: S221 app/Http/Controllers/ReviewController.php:54:        $this->authorize('delete', $review);
  - 事実: 該当なし（ValidatesRequests 由来の $this->validate 呼び出し） S222 grep -rn 'validate(' app/Http/Controllers → 出力0件（exit=1）
