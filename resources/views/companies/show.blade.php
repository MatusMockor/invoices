                                @if($company->ico)
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('IČO:') }}</span>
                                    <p class="font-medium">{{ $company->ico }}</p>
                                </div>
                                @endif
                                
                                @if($company->dic)
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('DIČ:') }}</span>
                                    <p class="font-medium">{{ $company->dic }}</p>
                                </div>
                                @endif
                                
                                @if($company->ic_dph)
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('IČ DPH:') }}</span>
                                    <p class="font-medium">{{ $company->ic_dph }}</p>
                                </div>
                                @endif
