package com.ecommerce.inventory.grpc;

import static io.grpc.MethodDescriptor.generateFullMethodName;

/**
 * <pre>
 * ===== تعريف الخدمة =====
 * </pre>
 */
@javax.annotation.Generated(
    value = "by gRPC proto compiler (version 1.68.1)",
    comments = "Source: inventory.proto")
@io.grpc.stub.annotations.GrpcGenerated
public final class InventoryServiceGrpc {

  private InventoryServiceGrpc() {}

  public static final java.lang.String SERVICE_NAME = "inventory.InventoryService";

  // Static method descriptors that strictly reflect the proto.
  private static volatile io.grpc.MethodDescriptor<com.ecommerce.inventory.grpc.GetStockRequest,
      com.ecommerce.inventory.grpc.GetStockReply> getGetStockMethod;

  @io.grpc.stub.annotations.RpcMethod(
      fullMethodName = SERVICE_NAME + '/' + "GetStock",
      requestType = com.ecommerce.inventory.grpc.GetStockRequest.class,
      responseType = com.ecommerce.inventory.grpc.GetStockReply.class,
      methodType = io.grpc.MethodDescriptor.MethodType.UNARY)
  public static io.grpc.MethodDescriptor<com.ecommerce.inventory.grpc.GetStockRequest,
      com.ecommerce.inventory.grpc.GetStockReply> getGetStockMethod() {
    io.grpc.MethodDescriptor<com.ecommerce.inventory.grpc.GetStockRequest, com.ecommerce.inventory.grpc.GetStockReply> getGetStockMethod;
    if ((getGetStockMethod = InventoryServiceGrpc.getGetStockMethod) == null) {
      synchronized (InventoryServiceGrpc.class) {
        if ((getGetStockMethod = InventoryServiceGrpc.getGetStockMethod) == null) {
          InventoryServiceGrpc.getGetStockMethod = getGetStockMethod =
              io.grpc.MethodDescriptor.<com.ecommerce.inventory.grpc.GetStockRequest, com.ecommerce.inventory.grpc.GetStockReply>newBuilder()
              .setType(io.grpc.MethodDescriptor.MethodType.UNARY)
              .setFullMethodName(generateFullMethodName(SERVICE_NAME, "GetStock"))
              .setSampledToLocalTracing(true)
              .setRequestMarshaller(io.grpc.protobuf.ProtoUtils.marshaller(
                  com.ecommerce.inventory.grpc.GetStockRequest.getDefaultInstance()))
              .setResponseMarshaller(io.grpc.protobuf.ProtoUtils.marshaller(
                  com.ecommerce.inventory.grpc.GetStockReply.getDefaultInstance()))
              .setSchemaDescriptor(new InventoryServiceMethodDescriptorSupplier("GetStock"))
              .build();
        }
      }
    }
    return getGetStockMethod;
  }

  private static volatile io.grpc.MethodDescriptor<com.ecommerce.inventory.grpc.ReserveRequest,
      com.ecommerce.inventory.grpc.ReserveReply> getReserveMethod;

  @io.grpc.stub.annotations.RpcMethod(
      fullMethodName = SERVICE_NAME + '/' + "Reserve",
      requestType = com.ecommerce.inventory.grpc.ReserveRequest.class,
      responseType = com.ecommerce.inventory.grpc.ReserveReply.class,
      methodType = io.grpc.MethodDescriptor.MethodType.UNARY)
  public static io.grpc.MethodDescriptor<com.ecommerce.inventory.grpc.ReserveRequest,
      com.ecommerce.inventory.grpc.ReserveReply> getReserveMethod() {
    io.grpc.MethodDescriptor<com.ecommerce.inventory.grpc.ReserveRequest, com.ecommerce.inventory.grpc.ReserveReply> getReserveMethod;
    if ((getReserveMethod = InventoryServiceGrpc.getReserveMethod) == null) {
      synchronized (InventoryServiceGrpc.class) {
        if ((getReserveMethod = InventoryServiceGrpc.getReserveMethod) == null) {
          InventoryServiceGrpc.getReserveMethod = getReserveMethod =
              io.grpc.MethodDescriptor.<com.ecommerce.inventory.grpc.ReserveRequest, com.ecommerce.inventory.grpc.ReserveReply>newBuilder()
              .setType(io.grpc.MethodDescriptor.MethodType.UNARY)
              .setFullMethodName(generateFullMethodName(SERVICE_NAME, "Reserve"))
              .setSampledToLocalTracing(true)
              .setRequestMarshaller(io.grpc.protobuf.ProtoUtils.marshaller(
                  com.ecommerce.inventory.grpc.ReserveRequest.getDefaultInstance()))
              .setResponseMarshaller(io.grpc.protobuf.ProtoUtils.marshaller(
                  com.ecommerce.inventory.grpc.ReserveReply.getDefaultInstance()))
              .setSchemaDescriptor(new InventoryServiceMethodDescriptorSupplier("Reserve"))
              .build();
        }
      }
    }
    return getReserveMethod;
  }

  private static volatile io.grpc.MethodDescriptor<com.ecommerce.inventory.grpc.ReleaseRequest,
      com.ecommerce.inventory.grpc.ReleaseReply> getReleaseMethod;

  @io.grpc.stub.annotations.RpcMethod(
      fullMethodName = SERVICE_NAME + '/' + "Release",
      requestType = com.ecommerce.inventory.grpc.ReleaseRequest.class,
      responseType = com.ecommerce.inventory.grpc.ReleaseReply.class,
      methodType = io.grpc.MethodDescriptor.MethodType.UNARY)
  public static io.grpc.MethodDescriptor<com.ecommerce.inventory.grpc.ReleaseRequest,
      com.ecommerce.inventory.grpc.ReleaseReply> getReleaseMethod() {
    io.grpc.MethodDescriptor<com.ecommerce.inventory.grpc.ReleaseRequest, com.ecommerce.inventory.grpc.ReleaseReply> getReleaseMethod;
    if ((getReleaseMethod = InventoryServiceGrpc.getReleaseMethod) == null) {
      synchronized (InventoryServiceGrpc.class) {
        if ((getReleaseMethod = InventoryServiceGrpc.getReleaseMethod) == null) {
          InventoryServiceGrpc.getReleaseMethod = getReleaseMethod =
              io.grpc.MethodDescriptor.<com.ecommerce.inventory.grpc.ReleaseRequest, com.ecommerce.inventory.grpc.ReleaseReply>newBuilder()
              .setType(io.grpc.MethodDescriptor.MethodType.UNARY)
              .setFullMethodName(generateFullMethodName(SERVICE_NAME, "Release"))
              .setSampledToLocalTracing(true)
              .setRequestMarshaller(io.grpc.protobuf.ProtoUtils.marshaller(
                  com.ecommerce.inventory.grpc.ReleaseRequest.getDefaultInstance()))
              .setResponseMarshaller(io.grpc.protobuf.ProtoUtils.marshaller(
                  com.ecommerce.inventory.grpc.ReleaseReply.getDefaultInstance()))
              .setSchemaDescriptor(new InventoryServiceMethodDescriptorSupplier("Release"))
              .build();
        }
      }
    }
    return getReleaseMethod;
  }

  private static volatile io.grpc.MethodDescriptor<com.ecommerce.inventory.grpc.SetStockRequest,
      com.ecommerce.inventory.grpc.SetStockReply> getSetStockMethod;

  @io.grpc.stub.annotations.RpcMethod(
      fullMethodName = SERVICE_NAME + '/' + "SetStock",
      requestType = com.ecommerce.inventory.grpc.SetStockRequest.class,
      responseType = com.ecommerce.inventory.grpc.SetStockReply.class,
      methodType = io.grpc.MethodDescriptor.MethodType.UNARY)
  public static io.grpc.MethodDescriptor<com.ecommerce.inventory.grpc.SetStockRequest,
      com.ecommerce.inventory.grpc.SetStockReply> getSetStockMethod() {
    io.grpc.MethodDescriptor<com.ecommerce.inventory.grpc.SetStockRequest, com.ecommerce.inventory.grpc.SetStockReply> getSetStockMethod;
    if ((getSetStockMethod = InventoryServiceGrpc.getSetStockMethod) == null) {
      synchronized (InventoryServiceGrpc.class) {
        if ((getSetStockMethod = InventoryServiceGrpc.getSetStockMethod) == null) {
          InventoryServiceGrpc.getSetStockMethod = getSetStockMethod =
              io.grpc.MethodDescriptor.<com.ecommerce.inventory.grpc.SetStockRequest, com.ecommerce.inventory.grpc.SetStockReply>newBuilder()
              .setType(io.grpc.MethodDescriptor.MethodType.UNARY)
              .setFullMethodName(generateFullMethodName(SERVICE_NAME, "SetStock"))
              .setSampledToLocalTracing(true)
              .setRequestMarshaller(io.grpc.protobuf.ProtoUtils.marshaller(
                  com.ecommerce.inventory.grpc.SetStockRequest.getDefaultInstance()))
              .setResponseMarshaller(io.grpc.protobuf.ProtoUtils.marshaller(
                  com.ecommerce.inventory.grpc.SetStockReply.getDefaultInstance()))
              .setSchemaDescriptor(new InventoryServiceMethodDescriptorSupplier("SetStock"))
              .build();
        }
      }
    }
    return getSetStockMethod;
  }

  /**
   * Creates a new async stub that supports all call types for the service
   */
  public static InventoryServiceStub newStub(io.grpc.Channel channel) {
    io.grpc.stub.AbstractStub.StubFactory<InventoryServiceStub> factory =
      new io.grpc.stub.AbstractStub.StubFactory<InventoryServiceStub>() {
        @java.lang.Override
        public InventoryServiceStub newStub(io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
          return new InventoryServiceStub(channel, callOptions);
        }
      };
    return InventoryServiceStub.newStub(factory, channel);
  }

  /**
   * Creates a new blocking-style stub that supports unary and streaming output calls on the service
   */
  public static InventoryServiceBlockingStub newBlockingStub(
      io.grpc.Channel channel) {
    io.grpc.stub.AbstractStub.StubFactory<InventoryServiceBlockingStub> factory =
      new io.grpc.stub.AbstractStub.StubFactory<InventoryServiceBlockingStub>() {
        @java.lang.Override
        public InventoryServiceBlockingStub newStub(io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
          return new InventoryServiceBlockingStub(channel, callOptions);
        }
      };
    return InventoryServiceBlockingStub.newStub(factory, channel);
  }

  /**
   * Creates a new ListenableFuture-style stub that supports unary calls on the service
   */
  public static InventoryServiceFutureStub newFutureStub(
      io.grpc.Channel channel) {
    io.grpc.stub.AbstractStub.StubFactory<InventoryServiceFutureStub> factory =
      new io.grpc.stub.AbstractStub.StubFactory<InventoryServiceFutureStub>() {
        @java.lang.Override
        public InventoryServiceFutureStub newStub(io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
          return new InventoryServiceFutureStub(channel, callOptions);
        }
      };
    return InventoryServiceFutureStub.newStub(factory, channel);
  }

  /**
   * <pre>
   * ===== تعريف الخدمة =====
   * </pre>
   */
  public interface AsyncService {

    /**
     * <pre>
     * 1. معرفة الكمية المتوفرة لمنتج معين
     * </pre>
     */
    default void getStock(com.ecommerce.inventory.grpc.GetStockRequest request,
        io.grpc.stub.StreamObserver<com.ecommerce.inventory.grpc.GetStockReply> responseObserver) {
      io.grpc.stub.ServerCalls.asyncUnimplementedUnaryCall(getGetStockMethod(), responseObserver);
    }

    /**
     * <pre>
     * 2. حجز كميات عدة منتجات لطلب واحد (كل أو لا شيء)
     * </pre>
     */
    default void reserve(com.ecommerce.inventory.grpc.ReserveRequest request,
        io.grpc.stub.StreamObserver<com.ecommerce.inventory.grpc.ReserveReply> responseObserver) {
      io.grpc.stub.ServerCalls.asyncUnimplementedUnaryCall(getReserveMethod(), responseObserver);
    }

    /**
     * <pre>
     * 3. إرجاع الكميات المحجوزة إذا فشل إنشاء الطلب
     * </pre>
     */
    default void release(com.ecommerce.inventory.grpc.ReleaseRequest request,
        io.grpc.stub.StreamObserver<com.ecommerce.inventory.grpc.ReleaseReply> responseObserver) {
      io.grpc.stub.ServerCalls.asyncUnimplementedUnaryCall(getReleaseMethod(), responseObserver);
    }

    /**
     * <pre>
     * 4. ضبط الكمية الابتدائية (مفيد عند إضافة منتج جديد)
     * </pre>
     */
    default void setStock(com.ecommerce.inventory.grpc.SetStockRequest request,
        io.grpc.stub.StreamObserver<com.ecommerce.inventory.grpc.SetStockReply> responseObserver) {
      io.grpc.stub.ServerCalls.asyncUnimplementedUnaryCall(getSetStockMethod(), responseObserver);
    }
  }

  /**
   * Base class for the server implementation of the service InventoryService.
   * <pre>
   * ===== تعريف الخدمة =====
   * </pre>
   */
  public static abstract class InventoryServiceImplBase
      implements io.grpc.BindableService, AsyncService {

    @java.lang.Override public final io.grpc.ServerServiceDefinition bindService() {
      return InventoryServiceGrpc.bindService(this);
    }
  }

  /**
   * A stub to allow clients to do asynchronous rpc calls to service InventoryService.
   * <pre>
   * ===== تعريف الخدمة =====
   * </pre>
   */
  public static final class InventoryServiceStub
      extends io.grpc.stub.AbstractAsyncStub<InventoryServiceStub> {
    private InventoryServiceStub(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      super(channel, callOptions);
    }

    @java.lang.Override
    protected InventoryServiceStub build(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      return new InventoryServiceStub(channel, callOptions);
    }

    /**
     * <pre>
     * 1. معرفة الكمية المتوفرة لمنتج معين
     * </pre>
     */
    public void getStock(com.ecommerce.inventory.grpc.GetStockRequest request,
        io.grpc.stub.StreamObserver<com.ecommerce.inventory.grpc.GetStockReply> responseObserver) {
      io.grpc.stub.ClientCalls.asyncUnaryCall(
          getChannel().newCall(getGetStockMethod(), getCallOptions()), request, responseObserver);
    }

    /**
     * <pre>
     * 2. حجز كميات عدة منتجات لطلب واحد (كل أو لا شيء)
     * </pre>
     */
    public void reserve(com.ecommerce.inventory.grpc.ReserveRequest request,
        io.grpc.stub.StreamObserver<com.ecommerce.inventory.grpc.ReserveReply> responseObserver) {
      io.grpc.stub.ClientCalls.asyncUnaryCall(
          getChannel().newCall(getReserveMethod(), getCallOptions()), request, responseObserver);
    }

    /**
     * <pre>
     * 3. إرجاع الكميات المحجوزة إذا فشل إنشاء الطلب
     * </pre>
     */
    public void release(com.ecommerce.inventory.grpc.ReleaseRequest request,
        io.grpc.stub.StreamObserver<com.ecommerce.inventory.grpc.ReleaseReply> responseObserver) {
      io.grpc.stub.ClientCalls.asyncUnaryCall(
          getChannel().newCall(getReleaseMethod(), getCallOptions()), request, responseObserver);
    }

    /**
     * <pre>
     * 4. ضبط الكمية الابتدائية (مفيد عند إضافة منتج جديد)
     * </pre>
     */
    public void setStock(com.ecommerce.inventory.grpc.SetStockRequest request,
        io.grpc.stub.StreamObserver<com.ecommerce.inventory.grpc.SetStockReply> responseObserver) {
      io.grpc.stub.ClientCalls.asyncUnaryCall(
          getChannel().newCall(getSetStockMethod(), getCallOptions()), request, responseObserver);
    }
  }

  /**
   * A stub to allow clients to do synchronous rpc calls to service InventoryService.
   * <pre>
   * ===== تعريف الخدمة =====
   * </pre>
   */
  public static final class InventoryServiceBlockingStub
      extends io.grpc.stub.AbstractBlockingStub<InventoryServiceBlockingStub> {
    private InventoryServiceBlockingStub(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      super(channel, callOptions);
    }

    @java.lang.Override
    protected InventoryServiceBlockingStub build(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      return new InventoryServiceBlockingStub(channel, callOptions);
    }

    /**
     * <pre>
     * 1. معرفة الكمية المتوفرة لمنتج معين
     * </pre>
     */
    public com.ecommerce.inventory.grpc.GetStockReply getStock(com.ecommerce.inventory.grpc.GetStockRequest request) {
      return io.grpc.stub.ClientCalls.blockingUnaryCall(
          getChannel(), getGetStockMethod(), getCallOptions(), request);
    }

    /**
     * <pre>
     * 2. حجز كميات عدة منتجات لطلب واحد (كل أو لا شيء)
     * </pre>
     */
    public com.ecommerce.inventory.grpc.ReserveReply reserve(com.ecommerce.inventory.grpc.ReserveRequest request) {
      return io.grpc.stub.ClientCalls.blockingUnaryCall(
          getChannel(), getReserveMethod(), getCallOptions(), request);
    }

    /**
     * <pre>
     * 3. إرجاع الكميات المحجوزة إذا فشل إنشاء الطلب
     * </pre>
     */
    public com.ecommerce.inventory.grpc.ReleaseReply release(com.ecommerce.inventory.grpc.ReleaseRequest request) {
      return io.grpc.stub.ClientCalls.blockingUnaryCall(
          getChannel(), getReleaseMethod(), getCallOptions(), request);
    }

    /**
     * <pre>
     * 4. ضبط الكمية الابتدائية (مفيد عند إضافة منتج جديد)
     * </pre>
     */
    public com.ecommerce.inventory.grpc.SetStockReply setStock(com.ecommerce.inventory.grpc.SetStockRequest request) {
      return io.grpc.stub.ClientCalls.blockingUnaryCall(
          getChannel(), getSetStockMethod(), getCallOptions(), request);
    }
  }

  /**
   * A stub to allow clients to do ListenableFuture-style rpc calls to service InventoryService.
   * <pre>
   * ===== تعريف الخدمة =====
   * </pre>
   */
  public static final class InventoryServiceFutureStub
      extends io.grpc.stub.AbstractFutureStub<InventoryServiceFutureStub> {
    private InventoryServiceFutureStub(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      super(channel, callOptions);
    }

    @java.lang.Override
    protected InventoryServiceFutureStub build(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      return new InventoryServiceFutureStub(channel, callOptions);
    }

    /**
     * <pre>
     * 1. معرفة الكمية المتوفرة لمنتج معين
     * </pre>
     */
    public com.google.common.util.concurrent.ListenableFuture<com.ecommerce.inventory.grpc.GetStockReply> getStock(
        com.ecommerce.inventory.grpc.GetStockRequest request) {
      return io.grpc.stub.ClientCalls.futureUnaryCall(
          getChannel().newCall(getGetStockMethod(), getCallOptions()), request);
    }

    /**
     * <pre>
     * 2. حجز كميات عدة منتجات لطلب واحد (كل أو لا شيء)
     * </pre>
     */
    public com.google.common.util.concurrent.ListenableFuture<com.ecommerce.inventory.grpc.ReserveReply> reserve(
        com.ecommerce.inventory.grpc.ReserveRequest request) {
      return io.grpc.stub.ClientCalls.futureUnaryCall(
          getChannel().newCall(getReserveMethod(), getCallOptions()), request);
    }

    /**
     * <pre>
     * 3. إرجاع الكميات المحجوزة إذا فشل إنشاء الطلب
     * </pre>
     */
    public com.google.common.util.concurrent.ListenableFuture<com.ecommerce.inventory.grpc.ReleaseReply> release(
        com.ecommerce.inventory.grpc.ReleaseRequest request) {
      return io.grpc.stub.ClientCalls.futureUnaryCall(
          getChannel().newCall(getReleaseMethod(), getCallOptions()), request);
    }

    /**
     * <pre>
     * 4. ضبط الكمية الابتدائية (مفيد عند إضافة منتج جديد)
     * </pre>
     */
    public com.google.common.util.concurrent.ListenableFuture<com.ecommerce.inventory.grpc.SetStockReply> setStock(
        com.ecommerce.inventory.grpc.SetStockRequest request) {
      return io.grpc.stub.ClientCalls.futureUnaryCall(
          getChannel().newCall(getSetStockMethod(), getCallOptions()), request);
    }
  }

  private static final int METHODID_GET_STOCK = 0;
  private static final int METHODID_RESERVE = 1;
  private static final int METHODID_RELEASE = 2;
  private static final int METHODID_SET_STOCK = 3;

  private static final class MethodHandlers<Req, Resp> implements
      io.grpc.stub.ServerCalls.UnaryMethod<Req, Resp>,
      io.grpc.stub.ServerCalls.ServerStreamingMethod<Req, Resp>,
      io.grpc.stub.ServerCalls.ClientStreamingMethod<Req, Resp>,
      io.grpc.stub.ServerCalls.BidiStreamingMethod<Req, Resp> {
    private final AsyncService serviceImpl;
    private final int methodId;

    MethodHandlers(AsyncService serviceImpl, int methodId) {
      this.serviceImpl = serviceImpl;
      this.methodId = methodId;
    }

    @java.lang.Override
    @java.lang.SuppressWarnings("unchecked")
    public void invoke(Req request, io.grpc.stub.StreamObserver<Resp> responseObserver) {
      switch (methodId) {
        case METHODID_GET_STOCK:
          serviceImpl.getStock((com.ecommerce.inventory.grpc.GetStockRequest) request,
              (io.grpc.stub.StreamObserver<com.ecommerce.inventory.grpc.GetStockReply>) responseObserver);
          break;
        case METHODID_RESERVE:
          serviceImpl.reserve((com.ecommerce.inventory.grpc.ReserveRequest) request,
              (io.grpc.stub.StreamObserver<com.ecommerce.inventory.grpc.ReserveReply>) responseObserver);
          break;
        case METHODID_RELEASE:
          serviceImpl.release((com.ecommerce.inventory.grpc.ReleaseRequest) request,
              (io.grpc.stub.StreamObserver<com.ecommerce.inventory.grpc.ReleaseReply>) responseObserver);
          break;
        case METHODID_SET_STOCK:
          serviceImpl.setStock((com.ecommerce.inventory.grpc.SetStockRequest) request,
              (io.grpc.stub.StreamObserver<com.ecommerce.inventory.grpc.SetStockReply>) responseObserver);
          break;
        default:
          throw new AssertionError();
      }
    }

    @java.lang.Override
    @java.lang.SuppressWarnings("unchecked")
    public io.grpc.stub.StreamObserver<Req> invoke(
        io.grpc.stub.StreamObserver<Resp> responseObserver) {
      switch (methodId) {
        default:
          throw new AssertionError();
      }
    }
  }

  public static final io.grpc.ServerServiceDefinition bindService(AsyncService service) {
    return io.grpc.ServerServiceDefinition.builder(getServiceDescriptor())
        .addMethod(
          getGetStockMethod(),
          io.grpc.stub.ServerCalls.asyncUnaryCall(
            new MethodHandlers<
              com.ecommerce.inventory.grpc.GetStockRequest,
              com.ecommerce.inventory.grpc.GetStockReply>(
                service, METHODID_GET_STOCK)))
        .addMethod(
          getReserveMethod(),
          io.grpc.stub.ServerCalls.asyncUnaryCall(
            new MethodHandlers<
              com.ecommerce.inventory.grpc.ReserveRequest,
              com.ecommerce.inventory.grpc.ReserveReply>(
                service, METHODID_RESERVE)))
        .addMethod(
          getReleaseMethod(),
          io.grpc.stub.ServerCalls.asyncUnaryCall(
            new MethodHandlers<
              com.ecommerce.inventory.grpc.ReleaseRequest,
              com.ecommerce.inventory.grpc.ReleaseReply>(
                service, METHODID_RELEASE)))
        .addMethod(
          getSetStockMethod(),
          io.grpc.stub.ServerCalls.asyncUnaryCall(
            new MethodHandlers<
              com.ecommerce.inventory.grpc.SetStockRequest,
              com.ecommerce.inventory.grpc.SetStockReply>(
                service, METHODID_SET_STOCK)))
        .build();
  }

  private static abstract class InventoryServiceBaseDescriptorSupplier
      implements io.grpc.protobuf.ProtoFileDescriptorSupplier, io.grpc.protobuf.ProtoServiceDescriptorSupplier {
    InventoryServiceBaseDescriptorSupplier() {}

    @java.lang.Override
    public com.google.protobuf.Descriptors.FileDescriptor getFileDescriptor() {
      return com.ecommerce.inventory.grpc.InventoryProto.getDescriptor();
    }

    @java.lang.Override
    public com.google.protobuf.Descriptors.ServiceDescriptor getServiceDescriptor() {
      return getFileDescriptor().findServiceByName("InventoryService");
    }
  }

  private static final class InventoryServiceFileDescriptorSupplier
      extends InventoryServiceBaseDescriptorSupplier {
    InventoryServiceFileDescriptorSupplier() {}
  }

  private static final class InventoryServiceMethodDescriptorSupplier
      extends InventoryServiceBaseDescriptorSupplier
      implements io.grpc.protobuf.ProtoMethodDescriptorSupplier {
    private final java.lang.String methodName;

    InventoryServiceMethodDescriptorSupplier(java.lang.String methodName) {
      this.methodName = methodName;
    }

    @java.lang.Override
    public com.google.protobuf.Descriptors.MethodDescriptor getMethodDescriptor() {
      return getServiceDescriptor().findMethodByName(methodName);
    }
  }

  private static volatile io.grpc.ServiceDescriptor serviceDescriptor;

  public static io.grpc.ServiceDescriptor getServiceDescriptor() {
    io.grpc.ServiceDescriptor result = serviceDescriptor;
    if (result == null) {
      synchronized (InventoryServiceGrpc.class) {
        result = serviceDescriptor;
        if (result == null) {
          serviceDescriptor = result = io.grpc.ServiceDescriptor.newBuilder(SERVICE_NAME)
              .setSchemaDescriptor(new InventoryServiceFileDescriptorSupplier())
              .addMethod(getGetStockMethod())
              .addMethod(getReserveMethod())
              .addMethod(getReleaseMethod())
              .addMethod(getSetStockMethod())
              .build();
        }
      }
    }
    return result;
  }
}
