<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Tables\Table;
use App\Enums\UserBookStatus;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DetachBulkAction;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\Books\BookResource;
use Filament\Resources\RelationManagers\RelationManager;

class BooksRelationManager extends RelationManager
{
    protected static string $relationship = 'books';

    protected static ?string $relatedResource = BookResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns(array_merge(BookResource::table($table)->getColumns(), [
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        UserBookStatus::PlanToRead->value => 'primary',
                        UserBookStatus::Reading->value => 'info',
                        UserBookStatus::Read->value => 'success',
                        UserBookStatus::OnHold->value => 'gray',
                        UserBookStatus::Dropped->value => 'danger',
                        default => 'gray',
                    }),
            ]))
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // ...
                    DetachBulkAction::make(),
                ]),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('status')
                            ->options(fn () => collect(UserBookStatus::cases())->mapWithKeys(function ($status) {
                                return [$status->value => $status->value];
                            })->toArray())
                            ->required(),
                    ]),
                CreateAction::make(),
            ]);
    }
}
