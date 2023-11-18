@section('title', __('組分けツール - 組分けデータ入力'))

<x-instantGrouping-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('組分けツール - 組分けデータ入力') }}
        </h2>
    </x-slot>

    <div class="container md:max-w-7xl w-11/12 mx-auto mt-5 mb-5 p-8 border rounded-3xl border-gray-200 shadow bg-white min-h-screen">
        <div class="flex flex-col w-full">
            <form id="instant-grouping" action="{{ url('instant-grouping') }}" method="post">
            @csrf
            <div class="mb-4">
                <label for="name" class="form-label inline-block mb-1 text-gray-700">{{ __('大会名') }}</label>
                <input type="text" class="form-control block w-full md:w-8/12 lg:w-6/12 px-3 py-1.5 bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none" name="name" maxlength="255" required>
                <small class="block text-xs text-gray-600 mt-1">{{ __('大会名を入力してください。※必須') }}</small>
            </div>
            <div class="form-group mb-4">
                <label for="type" class="form-label inline-block mb-1 text-gray-700">{{ __('対戦形式') }}</label>
                <select name="type" id="tournament-type" class="form-select block w-8/12 sm:w-6/12 md:w-4/12 lg:w-3/12 px-3 py-1.5 bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none" required>
                    <option value="" disabled>{{ __('選択してください') }}</option>
                    <option value="12">{{ __('個人（1人x12）') }}</option>
                    <option value="6">{{ __('タッグ（2人x6）') }}</option>
                    <option value="4">{{ __('トリプルス（3人x4）') }}</option>
                    <option value="3">{{ __('フォーマンセル（4人x3）') }}</option>
                    <option value="2">{{ __('交流戦（6人x2）') }}</option>
                </select>
                <small class="block mt-1 text-xs text-gray-600">{{ __('組分け対象の対戦形式を選択してください。 ※必須') }}</small>
            </div>
            <div class="form-group mb-4">
                <label for="round" class="form-label inline-block mb-1 text-gray-700">{{ __('回戦') }}</label>
                <select name="round" class="form-select block w-8/12 sm:w-6/12 md:w-4/12 lg:w-3/12 px-3 py-1.5 bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none" required>
                    <option value="" disabled>{{ __('選択してください') }}</option>
                    <option value="1">{{ __('1回戦') }}</option>
                    <option value="2">{{ __('2回戦') }}</option>
                    <option value="3">{{ __('3回戦') }}</option>
                    <option value="4">{{ __('4回戦') }}</option>
                    <option value="5">{{ __('5回戦') }}</option>
                    <option value="6">{{ __('6回戦') }}</option>
                    <option value="7">{{ __('7回戦') }}</option>
                    <option value="8">{{ __('準々決勝') }}</option>
                    <option value="9">{{ __('準決勝') }}</option>
                    <option value="10">{{ __('決勝戦') }}</option>
                </select>
                <small class="block mt-1 text-xs text-gray-600">{{ __('組分け対象の回戦を選択してください。 ※必須') }}</small>
            </div>
            <div class="form-group mb-4">
                <label class="form-label inline-block mb-1">{{ __('進行役抽出用入力欄') }}</label>
                <small class="block text-xs text-gray-600">{{ __('Botの抽出結果、または全参加者リストをまとめて貼り付けてください。') }}</small>
                <small class="block text-xs text-gray-600">{{ __('対戦形式と入力された全参加者数から必要な進行役数を計算し、上から順に必要数を取得します。') }}</small>
                <small class="block text-xs text-gray-600">{{ __('進行役が足りない、またはそもそもの合計人数に間違いがある場合は警告メッセージが表示されます。（抽出処理は実行）') }}</small>
                <small class="block text-xs text-gray-600">{{ __('「抽出する」ボタンをクリックすると、進行役は進行役入力欄に、余剰進行役と一般参加者は一般参加者入力欄にそれぞれコピーします。') }}</small>
                <small class="block text-xs text-gray-600 mb-1">{{ __('※改行のみの行は削除した上で抽出しますが、スペースなど空文字が含まれる行は1行としてカウントします。不要な改行は入れないようにしてください。') }}</small>
                <textarea class="form-control block w-full focus:outline-none line-number" id="separate-player-input" name="separate-players" rows="30"></textarea>
                <button type="button" class="px-2 py-1 mt-3 rounded bg-green-500 text-white hover:bg-green-600" onclick="separatePlayer()">{{ __('抽出する') }}</button>
            </div>
            <div id="separate-player-alert-1"></div>
            <div id="separate-player-alert-2"></div>
            <div id="separate-player-result"></div>
            <div class="form-group my-4">
                <label for="host-players" class="form-label inline-block mb-1">{{ __('進行役入力欄') }}</label>
                <small class="block text-xs text-gray-600 mb-1">{{ __('この回戦で進行役を担当する進行役のみを入力してください。※必須') }}</small>
                <textarea class="form-control block w-full focus:outline-none line-number" id="hostplayer-input" name="host-players" rows="30" required></textarea>
                <small class="block mt-1 mb-1">{{ __('＜入力フォーマット＞') }}</small>
                <small class="block text-xs text-gray-600 mb-1">{{ __('大会参加名★進（フレンドコード）【登録順】 の形式で、1人（1チーム）ごとに改行してください。交流戦形式の場合はチームタグのみ入力してください。') }}</small>
                <small class="block text-xs text-gray-600">{{ __('個人：AAA★進（0000-0000-0000）【123】') }}</small>
                <small class="block text-xs text-gray-600">{{ __('タッグ：AAA★進（0000-0000-0000）BBB（0000-0000-0000）【123】') }}</small>
                <small class="block text-xs text-gray-600">{{ __('トリプルス：AAA★進（0000-0000-0000）BBB（0000-0000-0000）CCC（0000-0000-0000）【123】') }}</small>
                <small class="block text-xs text-gray-600">{{ __('フォーマンセル：AAA★進（0000-0000-0000）BBB（0000-0000-0000）CCC（0000-0000-0000）DDD（0000-0000-0000）【123】') }}</small>
                <small class="block text-xs text-gray-600">{{ __('交流戦：AAA【123】') }}</small>
            </div>
            <div class="form-group mb-4">
                <label for="normal-players" class="form-label inline-block mb-1">{{ __('一般参加者入力欄') }}</label>
                <small class="block text-xs text-gray-600 mb-1">{{ __('この回戦で進行役を担当しない進行役と一般参加者を合わせて入力してください。※必須') }}</small>
                <textarea class="form-control block w-full focus:outline-none line-number" id="normalplayer-input" name="normal-players" rows="30" required></textarea>
                <small class="block mt-1 mb-1">{{ __('＜入力フォーマット＞') }}</small>
                <small class="block text-xs text-gray-600 mb-1">{{ __('大会参加名（フレンドコード）【登録順】 の形式で、1人（1チーム）ごとに改行してください。進行役には大会参加名の後ろに★進を付けてください。交流戦形式の場合はチームタグのみ入力してください。') }}</small>
                <small class="block text-xs text-gray-600">{{ __('個人：AAA（0000-0000-0000）【123】') }}</small>
                <small class="block text-xs text-gray-600">{{ __('タッグ：AAA（0000-0000-0000）BBB（0000-0000-0000）【123】') }}</small>
                <small class="block text-xs text-gray-600">{{ __('トリプルス：AAA（0000-0000-0000）BBB（0000-0000-0000）CCC（0000-0000-0000）【123】') }}</small>
                <small class="block text-xs text-gray-600">{{ __('フォーマンセル：AAA（0000-0000-0000）BBB（0000-0000-0000）CCC（0000-0000-0000）DDD（0000-0000-0000）【123】') }}</small>
                <small class="block text-xs text-gray-600">{{ __('交流戦：AAA【123】') }}</small>
            </div>
            <div class="form-group mb-4">
                <small class="block mt-1 mb-1">{{ __('＜注意事項＞') }}</small>
                <small class="block mt-1 text-xs text-gray-600 mb-2">{{ __('以下の場合はアラートが表示され、組分け処理がエラーになります。エラーメッセージを確認の上、適宜修正してください。') }}</small>
                <small class="block mt-1 text-xs text-gray-600">{{ __('・入力行に完全な重複が存在する場合') }}</small>
                <small class="block mt-1 text-xs text-gray-600">{{ __('・合計参加者数を1組あたりの人数で割った時に、余りが発生する場合') }}</small>
                <small class="block mt-1 text-xs text-gray-600 mb-2">{{ __('・合計組数に対して進行役が不足している、または必要人数を超えている場合') }}</small>
                <small class="block mt-1 text-xs text-gray-600">{{ __('入力内容に問題がなければ、組分け処理が実行され、組分け結果ページが表示されます。（確認ページはありません）') }}</small>
            </div>
            <div>
                <button type="submit" name="check" id="check" class="px-3 py-2 mr-2 rounded bg-green-500 text-white hover:bg-green-600">{{ __('組分けテスト') }}</button>
                <button type="submit" name="execute" class="px-3 py-2 rounded bg-sky-400 text-white hover:bg-sky-500">{{ __('組分けを実行する') }}</button>
            </div>
            </form>
        </div>
        @if (session('grouping-error'))
        <div class="flex flex-col mt-4">
            @if (session('type') === "個人")
            <p>対戦形式： {{ session('type') }}</p>
            <p>合計参加者数： {{ session('all-players') }}</p>
            <p>一般参加者数： {{ session('normal-players') }}</p>
            <p>進行役数： {{ session('host-players') }}</p>
            @else
            <p>対戦形式： {{ session('type') }}</p>
            <p>合計参加チーム数： {{ session('all-players') }}</p>
            <p>一般参加チーム数： {{ session('normal-players') }}</p>
            <p>進行役チーム数： {{ session('host-players') }}</p>
            @endif
            @if (session('groups'))
            <p>合計組数： {{ session('groups') }}</p>
            @endif
            <p class="text-xl font-semibold my-2 text-red-500">{{ session('grouping-error') }}</p>
            <p class="text-red-500">{!! nl2br(session('error-message')) !!}</p>
        </div>
        @endif
        @if (session('grouping-success'))
        <div class="flex flex-col mt-4">
            <p class="text-xl text-green-500 font-semibold mb-2">{{ session('grouping-success') }}</p>
            @if (session('type') === "個人")
            <p>対戦形式： {{ session('type') }}</p>
            <p>合計参加者数： {{ session('all-players') }}</p>
            <p>一般参加者数： {{ session('normal-players') }}</p>
            <p>進行役数： {{ session('host-players') }}</p>
            <p>合計組数： {{ session('groups') }}</p>
            @else
            <p>対戦形式： {{ session('type') }}</p>
            <p>合計参加チーム数： {{ session('all-players') }}</p>
            <p>一般参加チーム数： {{ session('normal-players') }}</p>
            <p>進行役チーム数： {{ session('host-players') }}</p>
            <p>合計組数： {{ session('groups') }}</p>
            @endif
            <p class="text-xl mt-4 mb-1">仮組分け結果（実際に保存されるデータとは異なります）</p>
            {!! nl2br(e(session('grouping-result'))) !!}
        </div>
        @endif
    </div>
    <script>
        function separatePlayer() {
            let separatePlayerAlert1 = document.getElementById("separate-player-alert-1");
            let separatePlayerAlert2 = document.getElementById("separate-player-alert-2");
            let separatePlayerResult = document.getElementById("separate-player-result");
            separatePlayerAlert1.innerHTML = "";
            separatePlayerAlert2.innerHTML = "";
            separatePlayerResult.innerHTML = "";

            let allPlayerText = document.getElementById("separate-player-input").value.trim().replace(/(\r?\n)+/g,"\n").replace(/[\u200B-\u200D\u2028-\u202E\uFEFF]/g, "");

            if (!allPlayerText) {
                separatePlayerAlert1.innerHTML = '<p class="text-lg text-red-600">エラー：進行役抽出用入力欄に参加者が入力されていません。</p>';
                return;
            }

            const allPlayerArray = allPlayerText.split("\n");

            let hostPlayerText = "";
            let notHostPlayerText = "";
            let normalPlayerText = "";
            let inputHostPlayerCount = 0;
            let hostPlayerCount = 0;
            let notHostPlayerCount = 0;
            let normalPlayerCount = 0;
            const allPlayerCount = allPlayerArray.length;
            const tournamentType = document.getElementById("tournament-type").value;

            if (!tournamentType) {
                separatePlayerAlert1.innerHTML = '<p class="text-lg text-red-600">エラー：対戦形式が選択されていません。</p>';
                return;
            }

            const groupCount = Math.floor(allPlayerCount / tournamentType);

            allPlayerArray.forEach(function (player) {
                if (player.indexOf("進") !== -1) {
                    inputHostPlayerCount++;
                    if (inputHostPlayerCount > groupCount) {
                        notHostPlayerCount++;
                        notHostPlayerText += player + "\n";
                    } else {
                        hostPlayerCount++;
                        hostPlayerText += player + "\n";
                    }
                } else {
                    normalPlayerCount++;
                    normalPlayerText += player + "\n";
                }
            });

            normalPlayerText = notHostPlayerText + normalPlayerText;
            hostPlayerText = hostPlayerText.trim();
            normalPlayerText = normalPlayerText.trim();

            document.getElementById("hostplayer-input").value = hostPlayerText;
            document.getElementById("normalplayer-input").value = normalPlayerText;

            let remainder = allPlayerCount % tournamentType;

            if (remainder !== 0) {
                separatePlayerAlert1.innerHTML = 
                    '<p class="text-lg text-red-600">注意：合計参加者数を1組あたりの人数で割った時に余りがあります。（1組に満たない参加者がいる）</p>' + 
                    '<p class="text-lg text-red-600">補欠補充を忘れている、得点上位通過者の人数が違う、途中辞退者の発生による手動調整のコピペミスなどがないか確認してください。</p>' + 
                    '<p class="text-lg text-red-600">' + remainder + '行多い、または' + (tournamentType - remainder) + '行少ない</p>';
            }

            if (inputHostPlayerCount < groupCount) {
                separatePlayerAlert2.innerHTML = 
                    '<p class="text-lg text-red-600">注意：進行役が不足しています。進行役補正での入れ替え忘れがないか確認してください。</p>';
            }

            if (tournamentType === "12") {
                separatePlayerResult.innerHTML = 
                    '<p class="text-lg">【抽出結果】</p>' + 
                    '<p class="text-lg">' + '進行役数：' + hostPlayerCount + ' ＋ 余剰進行役数：' + notHostPlayerCount + ' ＋ 一般参加者数：' + normalPlayerCount + ' ＝ 合計参加者数：' + allPlayerCount + '</p>' + 
                    '<p class="text-lg">' + '合計参加者数：' + allPlayerCount + ' ÷ 1組あたりの人数：' + tournamentType + ' ＝ 合計組数：' + groupCount + '　' + remainder + '人余り</p>' + 
                    '<p class="text-lg">' + '進行役必要数：' + groupCount + '　 進行役不足数：' + (groupCount - hostPlayerCount) + '</p>';
            } else {
                separatePlayerResult.innerHTML = 
                    '<p class="text-lg">【抽出結果】</p>' + 
                    '<p class="text-lg">' + '進行役チーム数：' + hostPlayerCount + ' ＋ 余剰進行役チーム数：' + notHostPlayerCount + ' ＋ 一般参加チーム数：' + normalPlayerCount + ' ＝ 合計参加チーム数：' + allPlayerCount + '</p>' + 
                    '<p class="text-lg">' + '合計参加チーム数：' + allPlayerCount + ' ÷ 1組あたりのチーム数：' + tournamentType + ' ＝ 合計組数：' + groupCount + '　' + remainder + 'チーム余り</p>' + 
                    '<p class="text-lg">' + '進行役チーム必要数：' + groupCount + '　 進行役チーム不足数：' + (groupCount - hostPlayerCount) + '</p>';
            }
        }
    </script>
</x-instantGrouping-layout>
