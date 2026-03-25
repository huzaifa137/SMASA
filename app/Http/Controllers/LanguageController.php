<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;

class LanguageController extends Controller
{
    /**
     * Change the application language
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $language
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchLanguage(Request $request, $language)
    {
        // Validate the language
        $supportedLanguages = ['en', 'ar', 'sw'];

        if (!in_array($language, $supportedLanguages)) {
            return Redirect::back()->with('error', trans('common.language_change_failed'));
        }

        // If user is authenticated via Laravel Auth, update their language preference
        if (Auth::check()) {
            try {
                Auth::user()->update(['language' => $language]);
            } catch (\Exception $e) {
                return Redirect::back()->with('error', trans('common.language_change_failed'));
            }
        } else {
            // Support session-based login used in this app (LoggedAdmin / LoggedStudent)
            $sessionUserId = $request->session()->get('LoggedAdmin') ?? $request->session()->get('LoggedStudent');
            if ($sessionUserId) {
                try {
                    $user = \App\Models\User::find($sessionUserId);
                    if ($user) {
                        $user->language = $language;
                        $user->save();
                    }
                } catch (\Exception $e) {
                    return Redirect::back()->with('error', trans('common.language_change_failed'));
                }
            }
        }

        // Store in session
        $request->session()->put('locale', $language);

        // Set the application locale
        App::setLocale($language);

        return Redirect::back()->with('success', trans('common.language_changed'));
    }

    /**
     * Get all available languages
     *
     * @return array
     */
    public static function getAvailableLanguages()
    {
        return [
            'en' => trans('common.english'),
            'ar' => trans('common.arabic'),
            'sw' => trans('common.swahili'),
        ];
    }

    /**
     * Get current language
     *
     * @return string
     */
    public static function getCurrentLanguage()
    {
        return App::getLocale();
    }

    /**
     * Get language name from code
     *
     * @param  string  $code
     * @return string
     */
    public static function getLanguageName($code)
    {
        $languages = self::getAvailableLanguages();
        return $languages[$code] ?? $code;
    }
}
