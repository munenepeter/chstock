graph TD
    A[Web Interface] --> B[Authentication Layer]
    B --> C[Application Layer]
    
    subgraph Application Layer
        C --> D[Requisition Module]
        C --> E[Purchase Order Module]
        C --> F[Stock Management Module]
        C --> G[Supplier Management Module]
    end
    
    D --> H[Database Layer]
    E --> H
    F --> H
    G --> H
    
    subgraph Core Features
        D --> D1[RO Creation]
        D --> D2[RO Tracking]
        D --> D3[RO Reports]
        
        E --> E1[PO Creation]
        E --> E2[PO Tracking]
        E --> E3[Expiry Management]
        
        F --> F1[Stock Levels]
        F --> F2[Purchase Limits]
        
        G --> G1[Supplier Rating]
        G --> G2[Performance Tracking]
    end