<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Meraki\Core\Plugins\PluginManager;
use Meraki\Core\Plugins\PluginRepository;

final class PluginController extends Controller
{
    public function __construct(
        private readonly PluginManager $plugins,
        private readonly PluginRepository $repo,
    ) {}

    public function index(): View
    {
        $plugins = $this->plugins->all();
        $records = collect($this->repo->all())->keyBy('name');

        return view('admin.plugins.index', compact('plugins', 'records'));
    }

    public function activate(string $name): RedirectResponse
    {
        try {
            $this->plugins->activate($name);
            return back()->with('success', "Plugin \"{$name}\" đã được kích hoạt.");
        } catch (\Throwable $e) {
            return back()->with('error', "Không thể kích hoạt \"{$name}\": " . $e->getMessage());
        }
    }

    public function deactivate(string $name): RedirectResponse
    {
        try {
            $this->plugins->deactivate($name);
            return back()->with('success', "Plugin \"{$name}\" đã bị vô hiệu hóa.");
        } catch (\Throwable $e) {
            return back()->with('error', "Không thể vô hiệu hóa \"{$name}\": " . $e->getMessage());
        }
    }
}
