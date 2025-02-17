@section('title', __('組分けツール - 組分け結果'))

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('組分けツール - 組分け結果') }}
        </h2>
    </x-slot>

    <div class="container md:max-w-7xl w-11/12 mx-auto mt-5 mb-5 p-8 border rounded-3xl border-gray-200 shadow bg-white min-h-screen">
        <h3 class="text-xl mb-4">{{ $instantGrouping->name }} - {{ $instantGrouping->getRoundText() }} 組分け結果</h3>
        <div>
            <p>作成者：{{ $instantGrouping->user->name }}</p>
            <p>対戦形式：{{ $instantGrouping->getTournamentTypeText() }}</p>
        </div>
        <div class="my-3">
            <button type="button" class="my-1 mx-1 px-2 py-1 rounded bg-sky-400 text-white hover:bg-sky-500" onclick="copyToClipBoardAllGroup('result')">{{ __('全ての組み分け結果をコピー') }}</button>
        </div>
        <div class="text-xs text-gray-500 mb-2">
            <p>＜Discordコピペ用ボタン＞</p>
            <p>Discord上で正しく表示するために、一部の記号文字（ _ ~ ` : * | ）の前にバックスラッシュ（\）が挿入されます。</p>
            <p>Discordでは一度に2000文字までしか書き込めないため、5組ごとにボタンを出力しています。</p>
        </div>
        <div id="copy-button" class="flex flex-wrap mb-4"></div>
        <span id="result">{!! nl2br(e($instantGrouping->result)) !!}</span>
    </div>
    <script>
        function unescapeHTML(escapedHtml) {
            const doc = new DOMParser().parseFromString(escapedHtml, 'text/html');
            return doc.documentElement.textContent;
        }

        function copyToClipBoardAllGroup(id){
            const content = unescapeHTML(document.getElementById(id).innerHTML);
            navigator.clipboard.writeText(content.trimEnd());
        }

        function copyToClipBoardPerGroup(start, end){
            const groupingText = unescapeHTML(document.getElementById("result").innerHTML);
            const groupCount = (groupingText.match(/組/g) || []).length;
            let splitEndTarget = end + 1;
            let targetText = "";
            let regexStart = "";
            let regexEnd = "";
            let isLastGroup = end - start < 4 || groupCount === end;

            if (start === 1) {
                if (isLastGroup) {
                    targetText = groupingText;
                } else {
                    regexEnd = new RegExp("\n" + splitEndTarget + "組");
                    targetText = groupingText.substring(0, groupingText.search(regexEnd));
                }
            } else {
                if (isLastGroup) {
                    regexStart = new RegExp("\n" + start + "組");
                    targetText = groupingText.substring(groupingText.search(regexStart));
                } else {
                    regexStart = new RegExp("\n" + start + "組");
                    regexEnd = new RegExp("\n" + splitEndTarget + "組");
                    targetText = groupingText.substring(groupingText.search(regexStart), groupingText.search(regexEnd));
                }
            }

            const replacePattern = ['_', '~', '`', ':', '*', '|'];
            replacePattern.forEach((regex) => {
                targetText = targetText.replaceAll(regex, '\\' + regex);
            });

            navigator.clipboard.writeText(targetText.trim());
        }

        window.onload = (event) => {
            const groupingText = unescapeHTML(document.getElementById("result").innerHTML);
            const groupCount = (groupingText.match(/組/g) || []).length;
            const copyGroupCount = 5;
            const copyButtonOutputGroup1 = Math.floor(groupCount / copyGroupCount);
            const copyButtonOutputGroup2 = groupCount - (copyGroupCount * copyButtonOutputGroup1);

            let copyButtonText = "";
            let start = 0;
            let end = 0;

            if (copyButtonOutputGroup1 >= 1) {
                for (let i = 1; i <= copyButtonOutputGroup1; i++) {
                    end = copyGroupCount * i;
                    start = end - copyGroupCount + 1;
                    copyButtonText += "<button type=\"button\" class=\"my-1 mx-1 px-1 py-1 rounded bg-sky-400 text-white hover:bg-sky-500\" onclick=\"copyToClipBoardPerGroup(" + start + "," + end + ")\">" + start + "-" + end + "組をコピー</button>";
                }
            }

            if (copyButtonOutputGroup2 >= 1) {
                start = end >= 1 ? end + 1 : 1;
                end = start + copyButtonOutputGroup2 - 1;
                let buttonText = start === end ? start + "組をコピー" : start + "-" + end + "組をコピー";
                copyButtonText += "<button type=\"button\" class=\"my-1 mx-1 px-1 py-1 rounded bg-sky-400 text-white hover:bg-sky-500\" onclick=\"copyToClipBoardPerGroup(" + start + "," + end + ")\">" + buttonText + "</button>";
            }

            document.getElementById("copy-button").innerHTML = copyButtonText;
        }
    </script>
</x-app-layout>
