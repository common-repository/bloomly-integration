<?php
namespace Bloomly_Namespace;

class Ezoic_Integrator {
    private $ezRequest;
    private $ezResponse;
    private $ezContentCollector;
    private $ezFilter;
    private $ezEndpoints;
    private $ezDebug;

    public function __construct(iEzoic_Integration_Request $request,
                                iEzoic_Integration_Response $response,
                                iEzoic_Integration_Content_Collector $contentCollector,
                                iEzoic_Integration_Filter $filter,
                                iEzoic_Integration_Endpoints $endpoints,
                                iEzoic_Integration_Debug $debug ) {
        $this->ezRequest = $request;
        $this->ezResponse = $response;
        $this->ezContentCollector = $contentCollector;
        $this->ezFilter = $filter;
        $this->ezEndpoints = $endpoints;
        $this->ezDebug = $debug;
    }

    public function ApplyEzoicMiddleware() {
		//Get Orig Content
		$orig_content = $this->ezContentCollector->GetOrigContent();

	    if( $this->ezFilter->WeShouldReturnOrig() ) {
	    	//Do nothing this should just return our final content
        } elseif( $this->ezEndpoints->IsEzoicEndpoint() ) {
            $orig_content = $this->ezEndpoints->GetEndpointAsset();
        } elseif( $this->ezDebug->WeShouldDebug() ) {
			$orig_content .= $this->ezDebug->GetDebugInformation();
		} else {
            $response = $this->ezRequest->GetContentResponseFromEzoic( $orig_content );
			$orig_content = $this->ezResponse->HandleEzoicResponse( $orig_content, $response );
		}

	    echo $orig_content;
    }
}