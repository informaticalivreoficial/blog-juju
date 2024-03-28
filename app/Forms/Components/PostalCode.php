<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Livewire\Component as Livewire;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class PostalCode extends TextInput
{
    public function viaCep(
        string $errorMessage = 'Cep Inválido',
        array $setFields = []
    ): static
    {
        $viaCepRequest = function(
            $state,
            $livewire,
            $set,
            $component,
            string $errorMessage,
            array $setFields
        ){
            $livewire->validateOnly($component->getKey());            

            $request = Http::get("viacep.com.br/ws/$state/json/")->json();

            foreach($setFields as $key => $value){
                $set($key, $request[$value] ?? null);
            }

            if(Arr::has($request, 'erro')){
                throw ValidationException::withMessages([
                    $component->getKey() => $errorMessage
                ]);
            }//dd($request);
        };

        $this->mask('99999-999')->required()->minLength(9)
        ->suffixAction(
            Action::make('search-action')
            ->label('Buscar Cep')            
            ->icon('heroicon-o-magnifying-glass')
            ->action(function($state, Livewire $livewire, Set $set, Component $component) use ($errorMessage, $setFields, $viaCepRequest){
                $viaCepRequest($state, $livewire, $set, $component, $errorMessage, $setFields);
            })
        )
        ->afterStateUpdated(function($state, Livewire $livewire, Set $set, Component $component) use($errorMessage, $setFields, $viaCepRequest){
            $viaCepRequest($state, $livewire, $set, $component, $errorMessage, $setFields);
        });
        return $this;
    }
}         