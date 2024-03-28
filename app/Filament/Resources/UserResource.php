<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Forms\Components\PostalCode;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Password as RulesPassword;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Usuários';

    protected static ?string $slug = 'usuarios';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        FileUpload::make('avatar')
                            ->disk('s3')
                            ->visibility('private')
                            ->directory(env('AWS_PASTA') . 'usuarios')
                            ->image()->imageEditor(), 
                    ])->columnSpan(1),

                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome Completo')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('birthday')
                            ->label('Data de Nascimento')
                            ->format('d/m/Y'),
                        Select::make('gender')->label('Genero')
                                ->options([
                                    'masculino' => 'Masculino',
                                    'feminino' => 'Feminino',
                                ])->placeholder('Selecione'),
                        Select::make('civil_status')->label('Estado Civil')
                                ->options([
                                    'solteiro' => 'Solteiro',
                                    'casado' => 'Casado',
                                    'separado' => 'Separado',
                                    'divorciado' => 'Divorciado',
                                    'viuvo' => 'Viúvo(a)',
                                ])->placeholder('Selecione'),
                        TextInput::make('cpf')->mask('999.999.999-99')->label('CPF')->required()->maxLength(255),
                        TextInput::make('rg')->mask('99.999.999-9')->label('RG')->maxLength(255),
                        TextInput::make('rg_expedition')->label('Órgão Expedidor')->maxLength(255),
                        TextInput::make('naturalness')->label('Naturalidade')->maxLength(255),
                    ])->columnSpan(2)->columns(2),

                Section::make('Redes Sociais')
                    ->schema([
                        TextInput::make('facebook')->label('Facebook')->maxLength(255),
                        TextInput::make('twitter')->label('Twitter')->maxLength(255),
                        TextInput::make('instagram')->label('Instagram')->maxLength(255),
                    ])->columns(3),

                Section::make('Endereço')
                    ->schema([
                        PostalCode::make('postcode')
                                ->label('CEP')
                                ->viaCep(
                                    setFields: [
                                    'street' => 'logradouro', 
                                    'neighborhood' => 'bairro', 
                                    'state' => 'uf', 
                                    'city' => 'localidade',
                                    'complement' => 'complemento',
                                    ]
                                ),
                        TextInput::make('state')->label('Estado')->maxLength(255),
                        TextInput::make('city')->label('Cidade')->maxLength(255),
                        TextInput::make('street')->label('Rua')->maxLength(255),
                        TextInput::make('neighborhood')->label('Bairro')->maxLength(255),
                        TextInput::make('complement')->label('Complemento')->maxLength(255),
                    ])->collapsible()->columns(3),

                Section::make('Contato')
                    ->schema([
                        TextInput::make('phone')->mask('(99) 9999-9999')->label('Residencial')->maxLength(255),
                        TextInput::make('cell_phone')->mask('(99) 99999-9999')->label('Celular')->maxLength(255)->required(),
                        TextInput::make('whatsapp')->mask('(99) 99999-9999')->label('WhatsApp')->maxLength(255),
                        TextInput::make('additional_email')->label('E-mail Alternativo')->maxLength(255),
                        TextInput::make('skype')->label('Skype')->maxLength(255),
                        TextInput::make('telegram')->label('Telegram')->maxLength(255),
                    ])->collapsible()->columnSpan(2)->columns(3),

                Section::make('Acesso')
                    ->schema([
                        TextInput::make('email')->email()->maxLength(255)->required(),
                        TextInput::make('password')
                                ->revealable()
                                ->password()
                                ->visibleOn('create')
                                ->rule(RulesPassword::default())->maxLength(255),
                        TextInput::make('password_confirmation')
                                ->password()
                                ->same('password')
                                ->visibleOn('create')
                                ->rule(RulesPassword::default())->maxLength(255)
                    ])->collapsible()->columnSpan(1),

                
                Forms\Components\Select::make('role')
                    ->options(User::ROLES)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->columns([
                ImageColumn::make('avatar')->label('Foto')->circular(),
                TextColumn::make('name')->label('Nome')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),                
                TextColumn::make('created_at')->label('Data')->dateTime('d/m/Y')->sortable(),
                ToggleColumn::make('status'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Usuário');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Usuários');
    }
}