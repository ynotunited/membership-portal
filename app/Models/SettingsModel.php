<?php

namespace App\Models;

class SettingsModel extends BaseModel
{
    public function getSettings()
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM settings WHERE id = 1");
        $stmt->execute();
        $result = $stmt->fetch();
        
        // Return default settings if none found
        if (!$result) {
            return [
                'id' => 1,
                'system_name' => 'GAFCONL',
                'logo' => '',
                'currency' => 'N'
            ];
        }
        
        return $result;
    }

    public function updateSettings($data)
    {
        // Check if settings exist
        $existingSettings = $this->getSettings();
        
        if ($existingSettings['id']) {
            // Update existing settings
            $sql = "UPDATE settings SET 
                    system_name = :system_name,
                    currency = :currency";
            
            $params = [
                'system_name' => $data['system_name'],
                'currency' => $data['currency']
            ];
            
            // Add logo if provided
            if (isset($data['logo'])) {
                $sql .= ", logo = :logo";
                $params['logo'] = $data['logo'];
            }
            
            $sql .= " WHERE id = 1";
            
            $stmt = $this->getConnection()->prepare($sql);
            return $stmt->execute($params);
        } else {
            // Insert new settings
            $stmt = $this->getConnection()->prepare("
                INSERT INTO settings (id, system_name, logo, currency) 
                VALUES (1, :system_name, :logo, :currency)
            ");
            
            return $stmt->execute([
                'system_name' => $data['system_name'],
                'logo' => $data['logo'] ?? '',
                'currency' => $data['currency']
            ]);
        }
    }

    public function getSystemName()
    {
        $settings = $this->getSettings();
        return $settings['system_name'] ?? 'GAFCONL';
    }

    public function getCurrency()
    {
        $stmt = $this->getConnection()->prepare("SELECT currency FROM settings LIMIT 1");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['currency'] ?? 'N';
    }

    public function getLogo()
    {
        $settings = $this->getSettings();
        return $settings['logo'] ?? '';
    }
} 