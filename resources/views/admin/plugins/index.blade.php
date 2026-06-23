@extends('admin.layout')

@section('title', 'Quản lý Plugin')

@section('content')
<div class="mb-6">
    <h1 class="text-lg font-semibold">Quản lý Plugin</h1>
    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1">Bật hoặc tắt các plugin đã được cài đặt.</p>
</div>

@if (empty($plugins))
    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Không có plugin nào được phát hiện.</p>
@else
    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <th class="text-left px-4 py-3 font-medium text-[#706f6c] dark:text-[#A1A09A]">Plugin</th>
                    <th class="text-left px-4 py-3 font-medium text-[#706f6c] dark:text-[#A1A09A]">Phiên bản</th>
                    <th class="text-left px-4 py-3 font-medium text-[#706f6c] dark:text-[#A1A09A]">Trạng thái</th>
                    <th class="text-left px-4 py-3 font-medium text-[#706f6c] dark:text-[#A1A09A]">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($plugins as $plugin)
                    @php
                        $record  = $records->get($plugin->id());
                        $active  = $record && (bool) $record->enabled;
                        $failed  = $record && ($record->status ?? '') === 'failed';
                        $installed = $record !== null;
                    @endphp
                    <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] last:border-0">
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $plugin->name() }}</div>
                            <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $plugin->id() }}</div>
                            @if ($plugin->description())
                                <div class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">{{ $plugin->description() }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-[#706f6c] dark:text-[#A1A09A]">
                            {{ $plugin->version() ?: '—' }}
                        </td>
                        <td class="px-4 py-3">
                            @if (! $installed)
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                    Chưa cài
                                </span>
                            @elseif ($failed)
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                                    ● Lỗi
                                </span>
                            @elseif ($active)
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                    ● Hoạt động
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                    ○ Tắt
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if (! $installed)
                                {{-- No action for uninstalled plugins --}}
                            @elseif ($active)
                                <form method="POST" action="{{ route('admin.plugins.deactivate', $plugin->id()) }}">
                                    @csrf
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs rounded-sm border border-[#e3e3e0] dark:border-[#3E3E3A] text-[#706f6c] dark:text-[#A1A09A] hover:border-red-400 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                                        Vô hiệu hóa
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.plugins.activate', $plugin->id()) }}">
                                    @csrf
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs rounded-sm border border-[#e3e3e0] dark:border-[#3E3E3A] text-[#706f6c] dark:text-[#A1A09A] hover:border-green-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">
                                        Kích hoạt
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
