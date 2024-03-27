<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationLabel = 'Artigos';

    protected static ?string $slug = 'artigos';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Conteúdo')->schema(
                    [
                        TextInput::make('title')
                            ->label('Título')
                            ->live()
                            ->required()->minLength(3)->maxLength(150)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation === 'edit') {
                                    return;
                                }

                                $set('slug', Str::slug($state));
                            }),
                        TextInput::make('slug')->required()->minLength(1)->unique(ignoreRecord: true)->maxLength(150),
                        RichEditor::make('content')
                            ->required()
                            ->label('Conteúdo')
                            ->fileAttachmentsDisk('s3')
                            ->fileAttachmentsDirectory(env('AWS_PASTA') . 'posts/images')
                            ->fileAttachmentsVisibility('private')                            
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'attachFiles',
                                'blockquote',
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'h2',
                                'h3',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'underline',
                                'undo',
                            ])
                    ]
                )->columns(2),
                Section::make('Meta')->schema(
                    [
                        FileUpload::make('thumb')
                            ->label('Imagem')
                            ->disk('s3')
                            ->visibility('private')
                            ->directory(env('AWS_PASTA') . 'posts')
                            ->image(),
                        DateTimePicker::make('published_at')
                            ->label('Publicar em')
                            ->nullable(),
                        Checkbox::make('featured')->label('Destaque'),
                        Select::make('user_id')
                            ->relationship('author', 'name')
                            ->label('Autor')
                            ->searchable()
                            ->required(),
                        Select::make('categories')
                            ->label('Categoria')
                            ->multiple()
                            ->relationship('categories', 'title')
                            ->searchable(),
                    ]
                ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumb'),
                TextColumn::make('title')->label('Título')->sortable()->searchable(),
                //TextColumn::make('author.name')->sortable()->searchable(),
                TextColumn::make('published_at')->label('Publicado')->date('d/m/Y')->sortable()->searchable(),
                CheckboxColumn::make('featured')->label('Destaque'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
