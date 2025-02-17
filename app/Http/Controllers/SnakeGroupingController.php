<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSnakeGroupingRequest;
use App\Models\SnakeGrouping;
use Illuminate\Support\Facades\Auth;

class SnakeGroupingController extends Controller
{
    CONST FFA = 12;
    CONST TAG = 6;
    CONST TRIPLES = 4;
    CONST FOURMANCELL = 3;
    CONST TEAM = 2;

    CONST PAGINATE = 10;

    public function __construct()
    {
        $this->middleware(['auth'])->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $snakeGroupings = SnakeGrouping::latest()->paginate(self::PAGINATE);

        return view('snake-grouping/index', ['snakeGroupings' => $snakeGroupings]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SnakeGrouping  $snakeGrouping
     * @return \Illuminate\Http\Response
     */
    public function show(SnakeGrouping $snakeGrouping)
    {
        return view('snake-grouping/show', ['snakeGrouping' => $snakeGrouping]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (Auth::user()->hasOrganizerRole()) {
            return redirect('/snake-grouping')->with('error', __('アクセス権限がありません。'));
        }

        return view('snake-grouping/create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSnakeGroupingRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSnakeGroupingRequest $request)
    {
        if (Auth::user()->hasOrganizerRole()) {
            return back()->with('error', __('アクセス権限がありません。'));
        }

        $validatedRequest = $request->validated();

        if ($request->has('check')) {
            return $this->check($validatedRequest, $request);
        }

        $hostPlayersText = $validatedRequest['host-players'];
        $normalPlayersText = $validatedRequest['normal-players'];
        $allPlayersText = $hostPlayersText . PHP_EOL . $normalPlayersText;

        $hostPlayers = $this->convertTextToArray($this->removeBackSlash($this->removeWhiteSpace($hostPlayersText)));
        $normalPlayers = $this->convertTextToArray($this->removeBackSlash($this->removeWhiteSpace($normalPlayersText)));
        $allPlayers = $this->convertTextToArray($this->removeBackSlash($this->removeWhiteSpace($allPlayersText)));

        $type = $validatedRequest['type'];
        $errors = [];

        $errors = $this->validateHostPlayerInput($hostPlayers, $errors, $type);
        $errors = $this->validateDuplicateRow($allPlayers, $hostPlayers, $normalPlayers, $errors);

        $allPlayerCount = count($allPlayers);
        $hostPlayerCount = count($hostPlayers);
        $normalPlayerCount = count($normalPlayers);
        $groupCount = (int)floor($allPlayerCount / $type);
        $remainder = $allPlayerCount % $type;

        $errors = $this->validateAllPlayerCount($errors, $allPlayerCount, $type, $groupCount, $remainder);
        $errors = $this->validateHostPlayerCount($errors, $hostPlayerCount, $groupCount);

        if ($errors) {
            $returnMessage = '';
            foreach ($errors as $error) {
                $returnMessage .= $error . PHP_EOL;
            }

            return back()->withInput()->with('error', $returnMessage);
        }

        $normalPlayerChunks = array_chunk($normalPlayers, $groupCount);
        $normalPlayerSnakedChunks = [];
        foreach ($normalPlayerChunks as $index => $normalPlayerChunk) {
            $normalPlayerSnakedChunks[] = $index % 2 === 0 ? array_reverse($normalPlayerChunk) : $normalPlayerChunk;
        }

        $groupingData = '';

        foreach ($hostPlayers as $index => $hostPlayer) {
            $row = $index + 1 . '組' . PHP_EOL;
            $row .= $hostPlayer . PHP_EOL;

            foreach ($normalPlayerSnakedChunks as $normalPlayerSnakedChunk) {
                $row .= $normalPlayerSnakedChunk[$index] . PHP_EOL;
            }

            $groupingData .= $row . '-' . PHP_EOL;
        }

        $snakeGrouping = new SnakeGrouping();

        $data = [
            'user_id' => Auth::id(),
            'name' => $validatedRequest['name'],
            'type' => $validatedRequest['type'],
            'round' => $validatedRequest['round'],
            'result' => $groupingData,
        ];

        $snakeGrouping->fill($data)->save();

        return redirect('snake-grouping/' . $snakeGrouping->id)->with('status', __('組分けデータを作成しました。'));
    }

    /**
     * 組分けテストを行い、結果をセッションに入れて元のページに戻す
     *
     * @param  $validatedRequest バリデーション済みリクエストデータ
     * @param  $request リクエストデータ
     * @return \Illuminate\Http\Response
     */
    public function check($validatedRequest, $request)
    {
        $hostPlayersText = $validatedRequest['host-players'];
        $normalPlayersText = $validatedRequest['normal-players'];
        $allPlayersText = $hostPlayersText . PHP_EOL . $normalPlayersText;

        $hostPlayers = $this->convertTextToArray($this->removeBackSlash($this->removeWhiteSpace($hostPlayersText)));
        $normalPlayers = $this->convertTextToArray($this->removeBackSlash($this->removeWhiteSpace($normalPlayersText)));
        $allPlayers = $this->convertTextToArray($this->removeBackSlash($this->removeWhiteSpace($allPlayersText)));

        $type = $validatedRequest['type'];
        $errors = [];

        $errors = $this->validateHostPlayerInput($hostPlayers, $errors, $type);
        $errors = $this->validateDuplicateRow($allPlayers, $hostPlayers, $normalPlayers, $errors);

        $allPlayerCount = count($allPlayers);
        $hostPlayerCount = count($hostPlayers);
        $normalPlayerCount = count($normalPlayers);
        $groupCount = (int)floor($allPlayerCount / $type);
        $remainder = $allPlayerCount % $type;

        $errors = $this->validateAllPlayerCount($errors, $allPlayerCount, $type, $groupCount, $remainder);
        $errors = $this->validateHostPlayerCount($errors, $hostPlayerCount, $groupCount);

        if ($errors) {
            $returnMessage = '';
            foreach ($errors as $error) {
                $returnMessage .= $error . PHP_EOL;
            }

            return redirect('snake-grouping/create#check')->withInput()->with([
                "grouping-error" => __('組分けテストNG：以下の入力エラーがあります。'),
                "error-message" => $returnMessage,
                "type" => $this->getTournamentTypeText($type),
                "all-players" => $allPlayerCount,
                'host-players' => $hostPlayerCount,
                'normal-players' => $normalPlayerCount,
                'groups' => $groupCount,
            ]);
        }

        $normalPlayerChunks = array_chunk($normalPlayers, $groupCount);
        $normalPlayerSnakedChunks = [];
        foreach ($normalPlayerChunks as $index => $normalPlayerChunk) {
            $normalPlayerSnakedChunks[] = $index % 2 === 0 ? array_reverse($normalPlayerChunk) : $normalPlayerChunk;
        }

        $groupingData = '';

        foreach ($hostPlayers as $index => $hostPlayer) {
            $row = $index + 1 . '組' . PHP_EOL;
            $row .= $hostPlayer . PHP_EOL;

            foreach ($normalPlayerSnakedChunks as $normalPlayerSnakedChunk) {
                $row .= $normalPlayerSnakedChunk[$index] . PHP_EOL;
            }

            $groupingData .= $row . '-' . PHP_EOL;
        }

        return redirect('snake-grouping/create#check')->withInput()->with([
            "grouping-success" => __('組分けテストOK：正常に組分けできます。'),
            "type" => $this->getTournamentTypeText($type),
            "all-players" => $allPlayerCount,
            'host-players' => $hostPlayerCount,
            'normal-players' => $normalPlayerCount,
            'groups' => $groupCount,
            'grouping-result' => $groupingData,
        ]);
    }

    /**
     * 文字列内の空白文字を取り除く（通常の半角スペース・全角スペースは許容する）
     */
    private function removeWhiteSpace(String $string): string
    {
        return preg_replace(
            '/(?:'
            .'(?:\x09)'
            .'|(?:\xc2\xa0)'
            .'|(?:\xe2\x80\x82)'
            .'|(?:\xe2\x80\x83)'
            .'|(?:\xe2\x80\x84)'
            .'|(?:\xe2\x80\x85)'
            .'|(?:\xe2\x80\x86)'
            .'|(?:\xe2\x80\x87)'
            .'|(?:\xe2\x80\x88)'
            .'|(?:\xe2\x80\x89)'
            .'|(?:\xe2\x80\x8a)'
            .'|(?:\xe2\x80\x8b)'
            .'|(?:\xef\xbb\xbf)'
            .')+/',
            '',
            $string
        );
    }

    /**
     * 文字列内のバックスラッシュを削除する
     */
    private function removeBackSlash(String $string): string
    {
        return preg_replace('/\\\/u', '', $string);
    }

    /**
     * 文字列を改行コードで区切って配列に変換する
     */
    private function convertTextToArray(String $string): array
    {
        return array_values(array_filter(array_map('trim', explode(PHP_EOL, $string)), 'strlen'));
    }

    /**
     * 進行役入力欄バリデーション（交流戦形式以外）
     */
    private function validateHostPlayerInput(Array $hostPlayers, Array $errors, String $type): array
    {
        if ((int)$type !== self::TEAM) {
            foreach ($hostPlayers as $index => $hostPlayer) {
                if (!preg_match('/進/u', $hostPlayer)) {
                    $errors[] = "【進行役入力欄チェックNG】";
                    break;
                }
            }
            foreach ($hostPlayers as $index => $hostPlayer) {
                if (!preg_match('/進/u', $hostPlayer)) {
                    $errors[] = '進行 ' . $index + 1 . '行目：大会参加名に ★進 が付いていません。 => ' . $hostPlayer;
                }
            }
        }

        return $errors;
    }

    /**
     * 入力行重複バリデーション
     */
    private function validateDuplicateRow(Array $allPlayers, Array $hostPlayers, Array $normalPlayers, Array $errors): array
    {
        if ($allPlayers !== array_unique($allPlayers)) {
            $duplicateRows = [];
            foreach (array_count_values($allPlayers) as $index => $value) {
                if ($value > 1) {
                    $duplicateRows[] = $index;
                }
            }

            if ($errors) {
                $errors[] = '';
            }

            $errors[] = "【重複行チェックNG】";
            $errors[] = "※以下の行が重複しています。";

            foreach ($duplicateRows as $row) {
                $regex = '/' . preg_quote($row, '/') . '/u';
                foreach ($hostPlayers as $index => $hostPlayer) {
                    if (preg_match($regex, $hostPlayer)) {
                        $errors[] = '進行 ' . $index + 1 . '行目：' . $hostPlayer;
                    }
                }
                foreach ($normalPlayers as $index => $normalPlayer) {
                    if (preg_match($regex, $normalPlayer)) {
                        $errors[] = '一般 ' . $index + 1 . '行目：' . $normalPlayer;
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * 合計参加者数バリデーション
     */
    private function validateAllPlayerCount(Array $errors, Int $allPlayerCount, String $type, Int $groupCount, Int $remainder): array
    {
        if ($errors) {
            $errors[] = '';
        }

        if ((int)$type !== self::FFA) {
            if ($allPlayerCount % $type !== 0) {
                $errors[] = "【合計参加チーム数チェックNG】";
                $errors[] = '合計参加チーム数が1組あたりのチーム数で割り切れないため組分けできません。（1組に満たない参加チームがいる）' . PHP_EOL . '補欠補充を忘れている、得点上位通過のチーム数が違う、途中辞退チームの発生による手動調整のコピペミスなどがないか確認してください。' . PHP_EOL . '合計参加チーム数：' . $allPlayerCount . ' ÷ 1組あたりのチーム数：' . $type . ' = 合計組数：' . $groupCount . '　' . $remainder . 'チーム余り' . PHP_EOL . '参加チームが' . $remainder . '行多い、または' . $type - $remainder . '行少ない';
            }
        } else {
            if ($allPlayerCount % $type !== 0) {
                $errors[] = "【合計参加者数チェックNG】";
                $errors[] = '合計参加者数が1組あたりの人数で割り切れないため組分けできません。（1組に満たない参加者がいる）' . PHP_EOL . '補欠補充を忘れている、得点上位通過者の人数が違う、途中辞退者の発生による手動調整のコピペミスなどがないか確認してください。' . PHP_EOL . '合計参加者数：' . $allPlayerCount . ' ÷ 1組あたりの人数：' . $type . ' = 合計組数：' . $groupCount . '　' . $remainder . '人余り' . PHP_EOL . '参加者が' . $remainder . '行多い、または' . $type - $remainder . '行少ない';
            }
        }

        return $errors;
    }

    /**
     * 進行役数バリデーション
     */
    private function validateHostPlayerCount(Array $errors, Int $hostPlayerCount, Int $groupCount): array
    {
        if ($hostPlayerCount !== $groupCount) {
            if ($errors) {
                $errors[] = '';
            }
            if ($hostPlayerCount > $groupCount) {
                $errors[] = "【進行役数チェックNG】";
                $errors[] = '進行役数と合計組数が合わないため組分けできません。（進行役が' . $hostPlayerCount - $groupCount . '人多い）' . PHP_EOL . '余った進行役は一般参加者入力欄に移動してください。' . PHP_EOL . "進行役数：" . $hostPlayerCount . " 合計組数：" . $groupCount;
            }
            if ($hostPlayerCount < $groupCount) {
                $errors[] = "【進行役数チェックNG】";
                $errors[] = '進行役数と合計組数が合わないため組分けできません。（進行役が' . $groupCount - $hostPlayerCount . '人足りない）' . PHP_EOL . '進行役補正での入れ替え忘れがないか確認してください。' . PHP_EOL . "進行役数：" . $hostPlayerCount . " 合計組数：" . $groupCount;
            }
        }

        return $errors;
    }

    /**
     * 対戦形式に対応するテキストを取得する
     */
    private function getTournamentTypeText(String $string): string
    {
        switch ($string) {
            case self::FFA:
                return __('個人');
                break;
            case self::TAG:
                return __('タッグ');
                break;
            case self::TRIPLES:
                return __('トリプルス');
                break;
            case self::FOURMANCELL:
                return __('フォーマンセル');
                break;
            case self::TEAM:
                return __('交流戦');
                break;
            default:
                return __('対戦形式が未設定');
        }
    }
}
