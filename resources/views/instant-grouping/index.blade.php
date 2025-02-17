@section('title', __('組分けツール - 組分け結果一覧'))

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('組分けツール - 組分け結果一覧') }}
        </h2>
    </x-slot>

    <div class="container md:max-w-7xl w-11/12 mx-auto mt-5 mb-5 p-8 border rounded-3xl border-gray-200 shadow bg-white min-h-screen">
        <div class="overflow-x-auto relative">
            <table class="border border-slate-300 whitespace-nowrap">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 border border-slate-300">{{ __('大会名') }}</th>
                        <th class="px-4 py-2 border border-slate-300">{{ __('回戦') }}</th>
                        <th class="px-4 py-2 border border-slate-300">{{ __('作成日時') }}</th>
                        <th class="px-4 py-2 border border-slate-300">{{ __('作成者') }}</th>
                        <th class="px-4 py-2 border border-slate-300">{{ __('削除理由') }}</th>
                        <th colspan=2 class="px-4 py-2 border border-slate-300"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($instantGroupings as $instantGrouping)
                    @if (!$instantGrouping->delete_reason)
                    <tr>
                    @else
                    <tr class="bg-gray-400">
                    @endif
                        <td class="px-4 py-2 border border-slate-300">{{ $instantGrouping->name }}</td>
                        <td class="px-4 py-2 border border-slate-300">{{ $instantGrouping->getRoundText() }}</td>
                        <td class="px-4 py-2 border border-slate-300">{{ $instantGrouping->created_at->isoFormat('YYYY/MM/DD HH:mm') }}</td>
                        <td class="px-4 py-2 border border-slate-300">{{ $instantGrouping->user->name }}</td>
                        <td class="px-4 py-2 border border-slate-300">{{ $instantGrouping->delete_reason }}</td>
                        <td class="px-1 py-2 border-y border-slate-300 text-center">
                            <button type="button" class="mx-1 px-2 py-1 rounded bg-sky-400 text-white hover:bg-sky-500" onclick="location.href='{{ url('instant-grouping/'. $instantGrouping->id) }}'">{{ __('表示') }}</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $instantGroupings->links() }}
        @if (Auth::check() && Auth::user()->hasOrganizerRole())
        <button type="button" class="mt-4 px-2 py-1 w-fit rounded bg-sky-400 text-white hover:bg-sky-500" onclick="location.href='{{ url('instant-grouping/create') }}'">{{ __('組分けデータを作成する') }}</button>
        @endif
        <div class="mt-5">
            <p>組み分けツールでは、大会の対戦形式に応じた組み分けデータを作成・保存することができます。</p>
            <p>現時点では、大会主催権限のあるユーザーのみが組み分けデータを作成できます。</p>
            <p>このツールでは、組み分け実行時に手動で結果を操作することはできず、実行前に結果を見てやり直すこともできません。</p>
            <p>また、組み分け実行後には必ずデータが保存され、主催者自身でデータ削除はできないため、意図的な結果操作ができません。</p>
            <p>組み分けデータの入力内容に補充漏れ・コピペミスなどがあっても、やり直しができませんので、入力時には十分注意してください。</p>
            <p class="text-red-600">※大会主催者様から「組み分けの時に入力ミスしました」などの個別連絡をいただいた場合に、管理者側で「組み分け失敗データ」として削除理由コメントを付ける場合があります。</p>
        </div>
        <p class="mt-3 text-sky-500 hover:text-blue-600"><a href="{{ url('grouping-manual') }}">組分けツールの使い方説明はこちら</a></p>
    </div>
</x-app-layout>
