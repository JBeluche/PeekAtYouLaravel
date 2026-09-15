<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingRequest;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use HttpResponses;

    public function show(Request $request): JsonResponse
    {
        return $this->success(null, new SettingResource($this->settingFor($request)));
    }

    public function update(UpdateSettingRequest $request): JsonResponse
    {
        $setting = $this->settingFor($request);
        $setting->update($request->validated());

        return $this->success('Settings updated', new SettingResource($setting->fresh()));
    }

    /**
     * Settings are per user and always exist, so fall back to creating the
     * default row for accounts made before settings were introduced.
     */
    private function settingFor(Request $request): Setting
    {
        return $request->user()->setting
            ?? Setting::create(['user_id' => $request->user()->id]);
    }
}
