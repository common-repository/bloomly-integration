<?php
namespace Bloomly_Namespace;

interface iEzoic_Integration_Endpoints {
    public function BustEndpointCache();
    public function IsEzoicEndpoint();
    public function GetEndpointAsset();
}