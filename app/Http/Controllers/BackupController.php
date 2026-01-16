<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupController extends Controller
{
    /**
     * Halaman backup database
     */
    public function index()
    {
        $title = 'Backup Database';
        $description = 'Kelola backup database klinik';

        // List backup files
        $backupPath = storage_path('app/backups');
        $backups = [];

        if (is_dir($backupPath)) {
            $files = scandir($backupPath);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                    $filePath = $backupPath . '/' . $file;
                    $backups[] = [
                        'name' => $file,
                        'size' => $this->formatBytes(filesize($filePath)),
                        'date' => date('Y-m-d H:i:s', filemtime($filePath)),
                    ];
                }
            }
            // Sort by date descending
            usort($backups, function ($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
        }

        return view('pages.clinic.backup.index', compact('title', 'description', 'backups'));
    }

    /**
     * Create database backup
     */
    public function create()
    {
        try {
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');

            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $backupPath = storage_path('app/backups');

            // Create backup directory if not exists
            if (!is_dir($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            $filePath = $backupPath . '/' . $filename;

            // Get all tables
            $tables = DB::select('SHOW TABLES');
            $tableKey = 'Tables_in_' . $database;

            $sql = "-- Database Backup\n";
            $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- Database: " . $database . "\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;

                // Get create table statement
                $createTable = DB::select("SHOW CREATE TABLE `$tableName`");
                $sql .= "-- Table: $tableName\n";
                $sql .= "DROP TABLE IF EXISTS `$tableName`;\n";
                $sql .= $createTable[0]->{'Create Table'} . ";\n\n";

                // Get table data
                $rows = DB::table($tableName)->get();

                if ($rows->count() > 0) {
                    $columns = array_keys((array) $rows->first());
                    $columnList = '`' . implode('`, `', $columns) . '`';

                    foreach ($rows as $row) {
                        $values = array_map(function ($value) {
                            if (is_null($value)) {
                                return 'NULL';
                            }
                            return "'" . addslashes($value) . "'";
                        }, (array) $row);

                        $sql .= "INSERT INTO `$tableName` ($columnList) VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $sql .= "\n";
                }
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

            // Write to file
            file_put_contents($filePath, $sql);

            return redirect()->route('clinic.backup.index', app()->getLocale())
                ->with('success', "Backup berhasil dibuat: $filename");

        } catch (\Exception $e) {
            return redirect()->route('clinic.backup.index', app()->getLocale())
                ->with('error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }

    /**
     * Download backup file
     */
    public function download($language, $filename)
    {
        $filePath = storage_path('app/backups/' . $filename);

        if (file_exists($filePath)) {
            return response()->download($filePath);
        }

        return redirect()->route('clinic.backup.index', app()->getLocale())
            ->with('error', 'File backup tidak ditemukan');
    }

    /**
     * Delete backup file
     */
    public function destroy($language, $filename)
    {
        $filePath = storage_path('app/backups/' . $filename);

        if (file_exists($filePath)) {
            unlink($filePath);
            return redirect()->route('clinic.backup.index', app()->getLocale())
                ->with('success', 'Backup berhasil dihapus');
        }

        return redirect()->route('clinic.backup.index', app()->getLocale())
            ->with('error', 'File backup tidak ditemukan');
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
